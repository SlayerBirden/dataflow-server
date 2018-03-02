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
use SlayerBirden\DataFlowServer\Db\Validator\Callback;
use SlayerBirden\DataFlowServer\Zend\InputFilter\ImprovedInputFilter;
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
                AddConfigAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    'AddConfigInputFilter',
                    LoggerInterface::class
                ],
                UpdateConfigAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    'UpdateConfigInputFilter',
                    LoggerInterface::class
                ],
                GetConfigsAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    LoggerInterface::class
                ],
                GetConfigAction::class => [
                    EntityManagerInterface::class,
                    ClassMethods::class,
                    LoggerInterface::class
                ],
                DeleteConfigAction::class => [
                    EntityManagerInterface::class,
                    LoggerInterface::class
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    Application::class => [
                        Factory\RoutesDelegator::class,
                    ]
                ]
            ],
            'doctrine' => [
                'paths' => [
                    'src/Db/Entities'
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
                            new Callback(function ($value, $context) {
                                return $value || ($context['url'] !== null);
                            }),
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
                            new Callback(function ($value, $context) {
                                return $value || ($context['url'] !== null);
                            }),
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
                            new Callback(function ($value, $context) {
                                return $value || ($context['url'] !== null);
                            }),
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
                            new Callback(function ($value, $context) {
                                return $value || ($context['url'] !== null);
                            }),
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
                            new Callback(function ($value, $context) {
                                return $value || ($context['url'] !== null);
                            }),
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
                            new Callback(function ($value, $context) {
                                return $value || ($context['url'] !== null);
                            }),
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
