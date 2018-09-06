<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain;

use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Domain\Controller\AddUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\DeleteUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\GetUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\GetUsersAction;
use SlayerBirden\DataFlowServer\Domain\Controller\UpdateUserAction;
use SlayerBirden\DataFlowServer\Domain\Factory\UserResourceMiddlewareFactory;
use SlayerBirden\DataFlowServer\Domain\Repository\UserRepository;
use SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory;
use Zend\Expressive\Application;
use Zend\Hydrator\ClassMethods;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => [
                UserRepository::class => [
                    ManagerRegistry::class,
                ],
                AddUserAction::class => [
                    ManagerRegistry::class,
                    ClassMethods::class,
                    'UserInputFilter',
                    LoggerInterface::class,
                ],
                UpdateUserAction::class => [
                    ManagerRegistry::class,
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
                    ManagerRegistry::class,
                    LoggerInterface::class,
                    ClassMethods::class,
                ],
            ],
            'doctrine' => [
                'entity_managers' => [
                    'default' => [
                        'paths' => [
                            'src/Domain/Entities',
                        ],
                    ],
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    Application::class => [
                        Factory\RoutesDelegator::class,
                    ]
                ],
                'factories' => [
                    'UserInputFilter' => ProxyFilterManagerFactory::class,
                    'UserResourceMiddleware' => UserResourceMiddlewareFactory::class,
                ],
            ],
            'input_filter_specs' => [
                'UserInputFilter' => [
                    'first' => [
                        'required' => true,
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
                        'required' => true,
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
                        'required' => true,
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
                ],
            ],
        ];
    }
}
