<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain;

use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Domain\Controller\AddUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\DeleteUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\GetUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\GetUsersAction;
use SlayerBirden\DataFlowServer\Domain\Controller\UpdateUserAction;
use SlayerBirden\DataFlowServer\Domain\Factory\UserResourceMiddlewareFactory;
use SlayerBirden\DataFlowServer\Domain\Repository\UserRepository;
use SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Hydrator\ClassMethods;
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
                'UserInputFilter' => $this->getUserInputFilterSpec(),
            ],
            'routes' => $this->getRoutesConfig(),
        ];
    }

    private function getUserInputFilterSpec(): array
    {
        return [
            'first' => [
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'notempty',
                    ],
                    [
                        'name' => 'alpha',
                    ],
                ]
            ],
            'last' => [
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'notempty',
                    ],
                    [
                        'name' => 'alpha',
                    ],
                ]
            ],
            'email' => [
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'notempty',
                    ],
                    [
                        'name' => 'emailAddress',
                    ],
                ],
            ],
        ];
    }

    private function getAbstractFactoryConfig(): array
    {
        return [
            UserRepository::class => [
                EntityManagerRegistry::class,
            ],
            AddUserAction::class => [
                EntityManagerRegistry::class,
                ClassMethods::class,
                'UserInputFilter',
                LoggerInterface::class,
            ],
            UpdateUserAction::class => [
                EntityManagerRegistry::class,
                ClassMethods::class,
                'UserInputFilter',
                LoggerInterface::class,
            ],
            GetUserAction::class => [
                ClassMethods::class,
            ],
            GetUsersAction::class => [
                UserRepository::class,
                LoggerInterface::class,
                ClassMethods::class,
            ],
            DeleteUserAction::class => [
                EntityManagerRegistry::class,
                LoggerInterface::class,
                ClassMethods::class,
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'entity_managers' => [
                'default' => [
                    'paths' => [
                        'src/Domain/Entities',
                    ],
                ],
            ],
        ];
    }

    private function getDependenciesConfig(): array
    {
        return [
            'factories' => [
                'UserInputFilter' => ProxyFilterManagerFactory::class,
                'UserResourceMiddleware' => UserResourceMiddlewareFactory::class,
            ],
        ];
    }

    public function getRoutesConfig(): array
    {
        return [
            [
                'path' => '/user/{id:\d+}',
                'middleware' => [
                    TokenMiddleware::class,
                    'UserResourceMiddleware',
                    GetUserAction::class,
                ],
                'name' => 'get_user',
                'allowed_methods' => ['GET'],
            ],
            [
                'path' => '/users',
                'middleware' => [
                    TokenMiddleware::class,
                    GetUsersAction::class,
                ],
                'name' => 'get_users',
                'allowed_methods' => ['GET'],
            ],
            [
                'path' => '/user',
                'middleware' => [
                    TokenMiddleware::class,
                    BodyParamsMiddleware::class,
                    AddUserAction::class,
                ],
                'name' => 'add_user',
                'allowed_methods' => ['POST'],
            ],
            [
                'path' => '/user/{id:\d+}',
                'middleware' => [
                    TokenMiddleware::class,
                    'UserResourceMiddleware',
                    BodyParamsMiddleware::class,
                    UpdateUserAction::class,
                ],
                'name' => 'update_user',
                'allowed_methods' => ['PUT'],
            ],
            [
                'path' => '/user/{id:\d+}',
                'middleware' => [
                    TokenMiddleware::class,
                    'UserResourceMiddleware',
                    DeleteUserAction::class
                ],
                'name' => 'delete_user',
                'allowed_methods' => ['DELETE'],
            ],
        ];
    }
}
