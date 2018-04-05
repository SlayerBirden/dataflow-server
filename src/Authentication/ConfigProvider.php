<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Controller\GenerateTemporaryTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Service\PasswordManager;
use SlayerBirden\DataFlowServer\Authentication\Service\TokenManager;
use SlayerBirden\DataFlowServer\Authentication\Validation\ResourceValidator;
use SlayerBirden\DataFlowServer\Authorization\PermissionManagerInterface;
use SlayerBirden\DataFlowServer\Extractor\RecursiveEntitiesExtractor;
use Zend\Expressive\Application;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => [
                GenerateTemporaryTokenAction::class => [
                    EntityManager::class,
                    'TokenInputFilter',
                    TokenManagerInterface::class,
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class,
                ],
                TokenMiddleware::class => [
                    EntityManager::class,
                ],
                PasswordManager::class => [
                    EntityManager::class,
                    LoggerInterface::class,
                ],
                TokenManager::class => [
                    PasswordManager::class,
                    EntityManager::class,
                    LoggerInterface::class,
                    PermissionManagerInterface::class,
                ],
            ],
            'doctrine' => [
                'paths' => [
                    'src/Authentication/Entities'
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    Application::class => [
                        Factory\RoutesDelegator::class,
                    ],
                ],
                'aliases' => [
                    TokenManagerInterface::class => TokenManager::class,
                    PasswordManagerInterface::class => PasswordManager::class,
                ],
            ],
            'validators' => [
                'aliases' => [
                    'resourcesValidator' => ResourceValidator::class,
                ],
                'factories' => [
                    ResourceValidator::class => InvokableFactory::class,
                ],
            ],
            'input_filter_specs' => [
                'TokenInputFilter' => [
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
            ]
        ];
    }
}
