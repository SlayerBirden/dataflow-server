<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Controller\CreatePasswordAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\GenerateTemporaryTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\GetTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\InvalidateTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Factory\PasswordExtractionFactory;
use SlayerBirden\DataFlowServer\Authentication\Factory\TokenExtractionFactory;
use SlayerBirden\DataFlowServer\Authentication\Hydrator\PasswordHydrator;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenResourceMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Service\PasswordManager;
use SlayerBirden\DataFlowServer\Authentication\Service\TokenManager;
use SlayerBirden\DataFlowServer\Authorization\PermissionManagerInterface;
use Zend\Expressive\Application;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;

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
                    'TokenExtraction',
                ],
                TokenMiddleware::class => [
                    EntityManager::class,
                ],
                PasswordManager::class => [
                    EntityManager::class,
                    LoggerInterface::class,
                ],
                TokenManager::class => [
                    PasswordManagerInterface::class,
                    EntityManager::class,
                    LoggerInterface::class,
                    PermissionManagerInterface::class,
                ],
                PasswordHydrator::class => [
                    PasswordManagerInterface::class,
                ],
                CreatePasswordAction::class => [
                    EntityManager::class,
                    'PasswordInputFilter',
                    LoggerInterface::class,
                    'PasswordExtraction',
                    PasswordHydrator::class,
                ],
                GetTokenAction::class => [
                    TokenManager::class,
                    'TokenExtraction',
                ],
                InvalidateTokenAction::class => [
                    EntityManager::class,
                    LoggerInterface::class,
                    'TokenExtraction',
                ],
                TokenResourceMiddleware::class => [
                    EntityManager::class,
                    LoggerInterface::class,
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
                'factories' => [
                    'TokenExtraction' => TokenExtractionFactory::class,
                    'PasswordExtraction' => PasswordExtractionFactory::class,
                ],
                'aliases' => [
                    TokenManagerInterface::class => TokenManager::class,
                    PasswordManagerInterface::class => PasswordManager::class,
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
                'PasswordInputFilter' => [
                    'password' => [
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'stringLength',
                                'options' => [
                                    'min' => 10,
                                ]
                            ],
                        ]
                    ],
                ],
                'UpdatePasswordInputFilter' => [
                    'new_password' => [
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'stringLength',
                                'options' => [
                                    'min' => 10,
                                ]
                            ],
                        ]
                    ],
                ],
            ]
        ];
    }
}
