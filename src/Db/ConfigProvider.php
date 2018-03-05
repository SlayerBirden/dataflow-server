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
use SlayerBirden\DataFlowServer\Extractor\RecursiveEntitiesExtractor;
use Zend\Expressive\Application;
use Zend\Hydrator\ClassMethods;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\Validator\Callback;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke()
    {
        $eitherOrUrlValidator = new Callback(function ($value, $context) {
            return $value || ($context['url'] !== null);
        });
        $eitherOrUrlValidator->setMessage(
            "This is required field if 'url' is not set.",
            Callback::INVALID_VALUE
        );

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
                            $eitherOrUrlValidator
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
                            $eitherOrUrlValidator
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
                            $eitherOrUrlValidator
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
                            $eitherOrUrlValidator
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
                            $eitherOrUrlValidator
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
                            $eitherOrUrlValidator
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
