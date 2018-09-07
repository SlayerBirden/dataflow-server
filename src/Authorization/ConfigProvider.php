<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization;

use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authorization\Controller\GetResourcesAction;
use SlayerBirden\DataFlowServer\Authorization\Controller\SavePermissionsAction;
use SlayerBirden\DataFlowServer\Authorization\Factory\PermissionHydratorFactory;
use SlayerBirden\DataFlowServer\Authorization\Repository\PermissionRepository;
use SlayerBirden\DataFlowServer\Authorization\Service\HistoryManagement;
use SlayerBirden\DataFlowServer\Authorization\Service\PermissionManager;
use SlayerBirden\DataFlowServer\Authorization\Service\ResourceManager;
use SlayerBirden\DataFlowServer\Authorization\Validation\ResourceValidator;
use SlayerBirden\DataFlowServer\Authorization\Validation\ResourceValidatorFactory;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory;
use Zend\Expressive\Application;
use Zend\Expressive\Router\RouteCollector;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\Validator\NotEmpty;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => $this->getAbstractFactoryConfig(),
            'doctrine' => $this->getDoctrineConfig(),
            'dependencies' => $this->getDependenciesConfig(),
            'validators' => $this->getValidatorsConfig(),
            'input_filter_specs' => [
                'PermissionsInputFilter' => $this->getPermissionsInputFilterSpec(),
            ],
        ];
    }

    private function getPermissionsInputFilterSpec(): array
    {
        return [
            'resources' => [
                'validators' => [
                    [
                        'name' => 'notEmpty',
                        'break_chain_on_failure' => true,
                        'options' => [
                            'type' => NotEmpty::BOOLEAN | NotEmpty::NULL | NotEmpty::STRING
                        ]
                    ],
                    [
                        'name' => 'resourcesValidator',
                    ],
                ],
            ],
        ];
    }

    private function getAbstractFactoryConfig(): array
    {
        return [
            PermissionRepository::class => [
                EntityManagerRegistry::class,
            ],
            PermissionManager::class => [
                PermissionRepository::class,
            ],
            ResourceManager::class => [
                RouteCollector::class,
            ],
            GetResourcesAction::class => [
                ResourceManagerInterface::class,
            ],
            HistoryManagement::class => [
                EntityManagerRegistry::class,
            ],
            SavePermissionsAction::class => [
                EntityManagerRegistry::class,
                PermissionRepository::class,
                LoggerInterface::class,
                'PermissionsInputFilter',
                HistoryManagementInterface::class,
                'PermissionHydrator',
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'entity_managers' => [
                'default' => [
                    'paths' => [
                        'src/Authorization/Entities',
                    ],
                ],
            ],
        ];
    }

    private function getDependenciesConfig(): array
    {
        return [
            'delegators' => [
                Application::class => [
                    Factory\RoutesDelegator::class,
                ],
            ],
            'aliases' => [
                PermissionManagerInterface::class => PermissionManager::class,
                ResourceManagerInterface::class => ResourceManager::class,
                HistoryManagementInterface::class => HistoryManagement::class,
            ],
            'factories' => [
                'PermissionsInputFilter' => ProxyFilterManagerFactory::class,
                'PermissionHydrator' => PermissionHydratorFactory::class,
            ],
        ];
    }

    private function getValidatorsConfig(): array
    {
        return [
            'aliases' => [
                'resourcesValidator' => ResourceValidator::class,
                'resourceValidator' => ResourceValidator::class,
            ],
            'factories' => [
                ResourceValidator::class => ResourceValidatorFactory::class,
            ],
        ];
    }
}
