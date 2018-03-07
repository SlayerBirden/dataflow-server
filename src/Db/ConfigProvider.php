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
use SlayerBirden\DataFlowServer\Db\Validation\ConfigValidator;
use SlayerBirden\DataFlowServer\Extractor\RecursiveEntitiesExtractor;
use Zend\Expressive\Application;
use Zend\Hydrator\ClassMethods;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Validator\Callback;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            ConfigAbstractFactory::class => [
                AddConfigAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    'AddConfigInputFilter',
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class
                ],
                UpdateConfigAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class,
                ],
                GetConfigsAction::class => [
                    EntityManagerInterface::class,
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class,
                ],
                GetConfigAction::class => [
                    EntityManagerInterface::class,
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class,
                ],
                DeleteConfigAction::class => [
                    EntityManagerInterface::class,
                    LoggerInterface::class,
                    RecursiveEntitiesExtractor::class,
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    Application::class => [
                        Factory\RoutesDelegator::class,
                    ],
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
                'AddConfigInputFilter' => [
                    'title' => [
                        'name' => 'title',
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
                        'name' => 'dbname',
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
                        'name' => 'user',
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
                        'name' => 'password',
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
                        'name' => 'host',
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
                        'name' => 'driver',
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
                        'name' => 'port',
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
                        'name' => 'url',
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
