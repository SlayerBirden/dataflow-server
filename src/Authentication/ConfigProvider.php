<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication;

use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Controller\CreatePasswordAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\GenerateTemporaryTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\GetTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\InvalidateTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\InvalidateTokensAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\UpdatePasswordAction;
use SlayerBirden\DataFlowServer\Authentication\Factory\PasswordHydratorFactory;
use SlayerBirden\DataFlowServer\Authentication\Factory\TokenHydratorFactory;
use SlayerBirden\DataFlowServer\Authentication\Factory\TokenResourceMiddlewareFactory;
use SlayerBirden\DataFlowServer\Authentication\Hydrator\Strategy\HashStrategy;
use SlayerBirden\DataFlowServer\Authentication\Middleware\ActivePasswordMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Middleware\PasswordConfirmationMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Repository\GrantRepository;
use SlayerBirden\DataFlowServer\Authentication\Repository\PasswordRepository;
use SlayerBirden\DataFlowServer\Authentication\Repository\TokenRepository;
use SlayerBirden\DataFlowServer\Authentication\Service\PasswordManager;
use SlayerBirden\DataFlowServer\Authentication\Service\TokenManager;
use SlayerBirden\DataFlowServer\Authorization\PermissionManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Repository\UserRepository;
use SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory;
use Zend\Expressive\Application;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => [
                TokenRepository::class => [
                    ManagerRegistry::class,
                ],
                PasswordRepository::class => [
                    ManagerRegistry::class,
                ],
                GrantRepository::class => [
                    ManagerRegistry::class,
                ],
                GenerateTemporaryTokenAction::class => [
                    'TokenInputFilter',
                    TokenManagerInterface::class,
                    LoggerInterface::class,
                    'TokenHydrator',
                ],
                TokenMiddleware::class => [
                    TokenRepository::class,
                ],
                ActivePasswordMiddleware::class => [
                    PasswordRepository::class,
                ],
                HashStrategy::class => [
                    PasswordManager::class,
                ],
                PasswordConfirmationMiddleware::class => [
                    PasswordManager::class,
                ],
                PasswordManager::class => [
                    PasswordRepository::class,
                    LoggerInterface::class,
                ],
                TokenManager::class => [
                    ManagerRegistry::class,
                    UserRepository::class,
                    PasswordManagerInterface::class,
                    LoggerInterface::class,
                    PermissionManagerInterface::class,
                ],
                CreatePasswordAction::class => [
                    ManagerRegistry::class,
                    'PasswordInputFilter',
                    LoggerInterface::class,
                    'PasswordHydrator',
                ],
                UpdatePasswordAction::class => [
                    ManagerRegistry::class,
                    PasswordRepository::class,
                    'UpdatePasswordInputFilter',
                    LoggerInterface::class,
                    PasswordManager::class,
                    'PasswordHydrator',
                ],
                GetTokenAction::class => [
                    TokenManager::class,
                    'TokenHydrator',
                    'GetTokenInputFilter',
                ],
                InvalidateTokenAction::class => [
                    ManagerRegistry::class,
                    LoggerInterface::class,
                    'TokenHydrator',
                ],
                InvalidateTokensAction::class => [
                    ManagerRegistry::class,
                    TokenRepository::class,
                    UserRepository::class,
                    LoggerInterface::class,
                    'TokenHydrator',
                ],
            ],
            'doctrine' => [
                'entity_managers' => [
                    'default' => [
                        'paths' => [
                            'src/Authentication/Entities',
                        ],
                    ],
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    Application::class => [
                        Factory\RoutesDelegator::class,
                    ],
                ],
                'factories' => [
                    'TokenHydrator' => TokenHydratorFactory::class,
                    'PasswordHydrator' => PasswordHydratorFactory::class,
                    'TokenInputFilter' => ProxyFilterManagerFactory::class,
                    'PasswordInputFilter' => ProxyFilterManagerFactory::class,
                    'UpdatePasswordInputFilter' => ProxyFilterManagerFactory::class,
                    'GetTokenInputFilter' => ProxyFilterManagerFactory::class,
                    'TokenResourceMiddleware' => TokenResourceMiddlewareFactory::class,
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
                        'validators' => [
                            [
                                'name' => 'resourcesValidator',
                            ],
                        ],
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
                                ],
                            ],
                        ],
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
                                ],
                            ],
                        ],
                    ],
                ],
                'GetTokenInputFilter' => [
                    'resources' => [
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'resourcesValidator',
                            ],
                        ],
                    ],
                    'password' => [
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'notempty',
                            ],
                        ]
                    ],
                    'user' => [
                        'required' => true,
                        'validators' => [
                            [
                                'name' => 'notempty',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
