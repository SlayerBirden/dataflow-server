<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Controller\AddConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\DeleteConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\GetConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\GetConfigsAction;
use SlayerBirden\DataFlowServer\Db\Controller\UpdateConfigAction;
use SlayerBirden\DataFlowServer\Db\Factory\DbConfigExtractionFactory;
use SlayerBirden\DataFlowServer\Db\Middleware\DbConfigResourceMiddleware;
use SlayerBirden\DataFlowServer\Db\Validation\ConfigValidator;
use Zend\Expressive\Application;
use Zend\Hydrator\ClassMethods;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => [
                AddConfigAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    'ConfigInputFilter',
                    LoggerInterface::class,
                    'DbConfigExtraction',
                ],
                UpdateConfigAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    'ConfigInputFilter',
                    LoggerInterface::class,
                    'DbConfigExtraction',
                ],
                GetConfigsAction::class => [
                    EntityManagerInterface::class,
                    LoggerInterface::class,
                    'DbConfigExtraction',
                ],
                GetConfigAction::class => [
                    'DbConfigExtraction',
                ],
                DeleteConfigAction::class => [
                    EntityManagerInterface::class,
                    LoggerInterface::class,
                    'DbConfigExtraction',
                ],
                DbConfigResourceMiddleware::class => [
                    EntityManagerInterface::class,
                    LoggerInterface::class,
                ]
            ],
            'dependencies' => [
                'delegators' => [
                    Application::class => [
                        Factory\RoutesDelegator::class,
                    ],
                ],
                'factories' => [
                    'DbConfigExtraction' => DbConfigExtractionFactory::class,
                ],
            ],
            'doctrine' => [
                'paths' => [
                    'src/Db/Entities'
                ],
            ],
            'validators' => [
                'aliases' => [
                    'configValidator' => ConfigValidator::class,
                ],
                'factories' => [
                    ConfigValidator::class => InvokableFactory::class,
                ],
            ],
            'input_filter_specs' => [
                'ConfigInputFilter' => [
                    'title' => [
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
                        ]
                    ],
                    'dbname' => [
                        'required' => false,
                        'continue_if_empty' => true,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'configValidator',
                            ]
                        ]
                    ],
                    'user' => [
                        'required' => false,
                        'continue_if_empty' => true,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'configValidator',
                            ]
                        ]
                    ],
                    'password' => [
                        'required' => false,
                        'continue_if_empty' => true,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'configValidator',
                            ]
                        ]
                    ],
                    'host' => [
                        'required' => false,
                        'continue_if_empty' => true,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'configValidator',
                            ]
                        ]
                    ],
                    'driver' => [
                        'required' => false,
                        'continue_if_empty' => true,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'configValidator',
                            ]
                        ]
                    ],
                    'port' => [
                        'required' => false,
                        'continue_if_empty' => true,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'configValidator',
                            ]
                        ]
                    ],
                    'url' => [
                        'required' => false,
                        'filters' => [
                            [
                                'name' => 'stringtrim',
                            ]
                        ],
                    ],
                ]
            ]
        ];
    }
}
