<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication;

use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Controller\CreatePasswordAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\GenerateTemporaryTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\GetTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\InvalidateTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\InvalidateTokensAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\UpdatePasswordAction;
use SlayerBirden\DataFlowServer\Authentication\Factory\PasswordHydratorFactory;
use SlayerBirden\DataFlowServer\Authentication\Factory\PasswordRepositoryFactory;
use SlayerBirden\DataFlowServer\Authentication\Factory\TokenHydratorFactory;
use SlayerBirden\DataFlowServer\Authentication\Factory\TokenRepositoryFactory;
use SlayerBirden\DataFlowServer\Authentication\Factory\TokenResourceMiddlewareFactory;
use SlayerBirden\DataFlowServer\Authentication\Hydrator\Strategy\HashStrategy;
use SlayerBirden\DataFlowServer\Authentication\Middleware\ActivePasswordMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Middleware\PasswordConfirmationMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Service\PasswordManager;
use SlayerBirden\DataFlowServer\Authentication\Service\TokenManager;
use SlayerBirden\DataFlowServer\Authorization\PermissionManagerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory;
use Zend\Expressive\Application;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => $this->getAbstractFactoryConfig(),
            'doctrine' => $this->getDoctrineConfig(),
            'dependencies' => $this->getDependenciesConfig(),
            'input_filter_specs' => [
                'TokenInputFilter' => $this->getTokenInputFilterSpec(),
                'PasswordInputFilter' => $this->getPasswordInputFilterSpec(),
                'UpdatePasswordInputFilter' => $this->getUpdatePasswordInputFilterSpec(),
                'GetTokenInputFilter' => $this->getGetTokenInputFilterSpec(),
            ],
        ];
    }

    private function getTokenInputFilterSpec(): array
    {
        return [
            'resources' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'resourcesValidator',
                    ],
                ],
            ],
        ];
    }

    private function getPasswordInputFilterSpec(): array
    {
        return [
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
        ];
    }

    private function getUpdatePasswordInputFilterSpec(): array
    {
        return [
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
        ];
    }

    private function getGetTokenInputFilterSpec(): array
    {
        return [
            'resources' => [
                'validators' => [
                    [
                        'name' => 'notempty',
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'resourcesValidator',
                    ],
                ],
            ],
            'password' => [
                'validators' => [
                    [
                        'name' => 'notempty',
                    ],
                ]
            ],
            'user' => [
                'validators' => [
                    [
                        'name' => 'notempty',
                    ],
                ],
            ],
        ];
    }

    private function getAbstractFactoryConfig(): array
    {
        return [
            GenerateTemporaryTokenAction::class => [
                'TokenInputFilter',
                TokenManagerInterface::class,
                LoggerInterface::class,
                'TokenHydrator',
            ],
            TokenMiddleware::class => [
                'TokenRepository',
            ],
            ActivePasswordMiddleware::class => [
                'PasswordRepository',
            ],
            HashStrategy::class => [
                PasswordManager::class,
            ],
            PasswordConfirmationMiddleware::class => [
                PasswordManager::class,
            ],
            PasswordManager::class => [
                'PasswordRepository',
                LoggerInterface::class,
            ],
            TokenManager::class => [
                EntityManagerRegistry::class,
                'UserRepository',
                PasswordManagerInterface::class,
                PermissionManagerInterface::class,
            ],
            CreatePasswordAction::class => [
                EntityManagerRegistry::class,
                'PasswordInputFilter',
                LoggerInterface::class,
                'PasswordHydrator',
            ],
            UpdatePasswordAction::class => [
                EntityManagerRegistry::class,
                'PasswordRepository',
                'UpdatePasswordInputFilter',
                LoggerInterface::class,
                PasswordManager::class,
                'PasswordHydrator',
            ],
            GetTokenAction::class => [
                TokenManager::class,
                'TokenHydrator',
                'GetTokenInputFilter',
                LoggerInterface::class,
            ],
            InvalidateTokenAction::class => [
                EntityManagerRegistry::class,
                LoggerInterface::class,
                'TokenHydrator',
            ],
            InvalidateTokensAction::class => [
                EntityManagerRegistry::class,
                'TokenRepository',
                'UserRepository',
                LoggerInterface::class,
                'TokenHydrator',
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'entity_managers' => [
                'default' => [
                    'paths' => [
                        'src/Authentication/Entities',
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
            'factories' => [
                'TokenHydrator' => TokenHydratorFactory::class,
                'PasswordHydrator' => PasswordHydratorFactory::class,
                'TokenInputFilter' => ProxyFilterManagerFactory::class,
                'PasswordInputFilter' => ProxyFilterManagerFactory::class,
                'UpdatePasswordInputFilter' => ProxyFilterManagerFactory::class,
                'GetTokenInputFilter' => ProxyFilterManagerFactory::class,
                'TokenResourceMiddleware' => TokenResourceMiddlewareFactory::class,
                'TokenRepository' => TokenRepositoryFactory::class,
                'PasswordRepository' => PasswordRepositoryFactory::class,
            ],
            'aliases' => [
                TokenManagerInterface::class => TokenManager::class,
                PasswordManagerInterface::class => PasswordManager::class,
            ],
        ];
    }
}
