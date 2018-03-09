<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Domain\Controller\AddUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\DeleteUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\GetUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\GetUsersAction;
use SlayerBirden\DataFlowServer\Domain\Controller\UpdateUserAction;
use SlayerBirden\DataFlowServer\Extractor\RecursiveEntitiesExtractor;
use Zend\Expressive\Application;
use Zend\Hydrator\ClassMethods;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => [
                AddUserAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    'UserInputFilter',
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class
                ],
                UpdateUserAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    'UserInputFilter',
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class,
                ],
                GetUserAction::class => [
                    EntityManagerInterface::class,
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class,
                ],
                GetUsersAction::class => [
                    EntityManagerInterface::class,
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class,
                ],
                DeleteUserAction::class => [
                    EntityManagerInterface::class,
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class,
                ],
            ],
            'doctrine' => [
                'paths' => [
                    'src/Domain/Entities'
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    Application::class => [
                        Factory\RoutesDelegator::class,
                    ]
                ]
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
