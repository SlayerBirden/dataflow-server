<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db;

use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Controller\AddConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\DeleteConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\GetConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\GetConfigsAction;
use SlayerBirden\DataFlowServer\Db\Controller\UpdateConfigAction;
use SlayerBirden\DataFlowServer\Db\Factory\DbConfigHydratorFactory;
use SlayerBirden\DataFlowServer\Db\Factory\DbConfigResourceMiddlewareFactory;
use SlayerBirden\DataFlowServer\Db\Repository\DbConfigurationRepository;
use SlayerBirden\DataFlowServer\Db\Validation\ConfigValidator;
use SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory;
use Zend\Expressive\Application;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => [
                DbConfigurationRepository::class => [
                    ManagerRegistry::class,
                ],
                AddConfigAction::class => [
                    ManagerRegistry::class,
                    'DbConfigHydrator',
                    'ConfigInputFilter',
                    LoggerInterface::class,
                ],
                UpdateConfigAction::class => [
                    ManagerRegistry::class,
                    'DbConfigHydrator',
                    'ConfigInputFilter',
                    LoggerInterface::class,
                ],
                GetConfigsAction::class => [
                    DbConfigurationRepository::class,
                    LoggerInterface::class,
                    'DbConfigHydrator',
                ],
                GetConfigAction::class => [
                    'DbConfigHydrator',
                ],
                DeleteConfigAction::class => [
                    ManagerRegistry::class,
                    LoggerInterface::class,
                    'DbConfigHydrator',
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    Application::class => [
                        Factory\RoutesDelegator::class,
                    ],
                ],
                'factories' => [
                    'DbConfigHydrator' => DbConfigHydratorFactory::class,
                    'ConfigInputFilter' => ProxyFilterManagerFactory::class,
                    'DbConfigResourceMiddleware' => DbConfigResourceMiddlewareFactory::class,
                ],
            ],
            'doctrine' => [
                'entity_managers' => [
                    'default' => [
                        'paths' => [
                            'src/Db/Entities',
                        ],
                    ],
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
