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
    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            ConfigAbstractFactory::class => [
                AddUserAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    'AddUserInputFilter',
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class
                ],
                UpdateUserAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    'UpdateUserInputFilter',
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
                'AddUserInputFilter' => [
                    'first' => [
                        'name' => 'firstname',
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
                        'name' => 'lastname',
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
                        'name' => 'email',
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
                        ]
                    ],
                ],
                'UpdateUserInputFilter' => [
                    'first' => [
                        'name' => 'firstname',
                        'required' => false,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'alpha',
                            ],
                        ]
                    ],
                    'last' => [
                        'name' => 'lastname',
                        'required' => false,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'alpha',
                            ],
                        ]
                    ],
                    'email' => [
                        'name' => 'email',
                        'required' => false,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'emailAddress',
                            ],
                        ]
                    ],
                ],
            ],
        ];
    }
}
