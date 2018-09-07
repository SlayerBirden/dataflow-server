<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db;

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
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory;
use Zend\Expressive\Application;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => $this->getAbstractFactoryConfig(),
            'dependencies' => $this->getDependenciesConfig(),
            'doctrine' => $this->getDoctrineConfig(),
            'validators' => $this->getValidatorsConfig(),
            'input_filter_specs' => [
                'ConfigInputFilter' => $this->getConfigInputFilterSpec(),
            ]
        ];
    }

    private function getConfigInputFilterSpec(): array
    {
        return [
            'title' => [
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
        ];
    }

    private function getAbstractFactoryConfig(): array
    {
        return [
            DbConfigurationRepository::class => [
                EntityManagerRegistry::class,
            ],
            AddConfigAction::class => [
                EntityManagerRegistry::class,
                'DbConfigHydrator',
                'ConfigInputFilter',
                LoggerInterface::class,
            ],
            UpdateConfigAction::class => [
                EntityManagerRegistry::class,
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
                EntityManagerRegistry::class,
                LoggerInterface::class,
                'DbConfigHydrator',
            ],
        ];
    }

    public function getDependenciesConfig(): array
    {
        return [
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
        ];
    }

    public function getDoctrineConfig(): array
    {
        return [
            'entity_managers' => [
                'default' => [
                    'paths' => [
                        'src/Db/Entities',
                    ],
                ],
            ],
        ];
    }

    public function getValidatorsConfig(): array
    {
        return [
            'aliases' => [
                'configValidator' => ConfigValidator::class,
            ],
            'factories' => [
                ConfigValidator::class => InvokableFactory::class,
            ],
        ];
    }
}
