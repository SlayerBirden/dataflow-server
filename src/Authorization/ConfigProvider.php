<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authorization\Controller\GetResourcesAction;
use SlayerBirden\DataFlowServer\Authorization\Controller\SavePermissionsAction;
use SlayerBirden\DataFlowServer\Authorization\Service\HistoryManagement;
use SlayerBirden\DataFlowServer\Authorization\Service\PermissionManager;
use SlayerBirden\DataFlowServer\Authorization\Service\ResourceManager;
use SlayerBirden\DataFlowServer\Authorization\Validation\ResourceValidator;
use SlayerBirden\DataFlowServer\Authorization\Validation\ResourceValidatorFactory;
use SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory;
use Zend\Expressive\Application;
use Zend\Expressive\Router\RouteCollector;
use Zend\Hydrator\ClassMethods;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => [
                PermissionManager::class => [
                    EntityManager::class,
                ],
                ResourceManager::class => [
                    RouteCollector::class,
                ],
                GetResourcesAction::class => [
                    ResourceManagerInterface::class,
                ],
                HistoryManagement::class => [
                    EntityManager::class,
                ],
                SavePermissionsAction::class => [
                    EntityManager::class,
                    LoggerInterface::class,
                    'PermissionsInputFilter',
                    HistoryManagementInterface::class,
                    ClassMethods::class,
                ],
            ],
            'doctrine' => [
                'paths' => [
                    'src/Authorization/Entities'
                ],
            ],
            'dependencies' => [
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
                ],
            ],
            'validators' => [
                'aliases' => [
                    'resourcesValidator' => ResourceValidator::class,
                    'resourceValidator' => ResourceValidator::class,
                ],
                'factories' => [
                    ResourceValidator::class => ResourceValidatorFactory::class,
                ],
            ],
            'input_filter_specs' => [
                'PermissionsInputFilter' => [
                    'resources' => [
                        'required' => true,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'resourcesValidator',
                            ],
                        ]
                    ],
                ],
            ],
        ];
    }
}
