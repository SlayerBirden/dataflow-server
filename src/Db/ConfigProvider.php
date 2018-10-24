<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db;

use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Db\Controller\AddConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\DeleteConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\GetConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\GetConfigsAction;
use SlayerBirden\DataFlowServer\Db\Controller\UpdateConfigAction;
use SlayerBirden\DataFlowServer\Db\Doctrine\Subscriber\Validation;
use SlayerBirden\DataFlowServer\Db\Factory\DbConfigHydratorFactory;
use SlayerBirden\DataFlowServer\Db\Factory\DbConfigResourceMiddlewareFactory;
use SlayerBirden\DataFlowServer\Db\Factory\DbConfigurationRepositoryFactory;
use SlayerBirden\DataFlowServer\Db\Factory\InputFilterMiddlewareFactory;
use SlayerBirden\DataFlowServer\Db\Validation\ConfigValidator;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Domain\Middleware\SetOwnerFilterMiddleware;
use SlayerBirden\DataFlowServer\Domain\Middleware\SetOwnerMiddleware;
use SlayerBirden\DataFlowServer\Domain\Middleware\ValidateOwnerMiddleware;
use SlayerBirden\DataFlowServer\Zend\InputFilter\ProxyFilterManagerFactory;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

final class ConfigProvider
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
            ],
            'routes' => $this->getRoutesConfig(),
        ];
    }

    private function getConfigInputFilterSpec(): array
    {
        return [
            'title' => [
                'required' => false,
                'continue_if_empty' => true,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'notempty',
                    ],
                ],
            ],
            'dbname' => [
                'required' => false,
                'continue_if_empty' => true,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'notempty',
                    ],
                ],
            ],
            'user' => [
                'required' => false,
                'continue_if_empty' => true,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'notempty',
                    ],
                ],
            ],
            'password' => [
                'required' => false,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ],
                ],
            ],
            'host' => [
                'required' => false,
                'continue_if_empty' => true,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'notempty',
                    ],
                ],
            ],
            'driver' => [
                'required' => false,
                'continue_if_empty' => true,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'inArray',
                        'options' => [
                            'haystack' => [
                                'mysql',
                                'pdo_mysql',
                                'pdo_sqlite',
                                'drizzle_pdo_mysql',
                                'pdo_pgsql'
                            ],
                            "strict" => \Zend\Validator\InArray::COMPARE_STRICT,
                            "messages" => [
                                \Zend\Validator\InArray::NOT_IN_ARRAY => 'Please use valid DBAL driver',
                            ],
                        ],
                    ],
                ],
            ],
            'port' => [
                'required' => false,
                'continue_if_empty' => true,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'digits',
                    ],
                ],
            ],
            'url' => [
                'required' => false,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ],
                ],
            ],
        ];
    }

    private function getAbstractFactoryConfig(): array
    {
        return [
            AddConfigAction::class => [
                EntityManagerRegistry::class,
                'DbConfigHydrator',
                LoggerInterface::class,
            ],
            UpdateConfigAction::class => [
                EntityManagerRegistry::class,
                'DbConfigHydrator',
                LoggerInterface::class,
            ],
            GetConfigsAction::class => [
                'DbConfigurationRepository',
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
            'factories' => [
                'DbConfigHydrator' => DbConfigHydratorFactory::class,
                'ConfigInputFilter' => ProxyFilterManagerFactory::class,
                'DbConfigResourceMiddleware' => DbConfigResourceMiddlewareFactory::class,
                'DbConfigurationRepository' => DbConfigurationRepositoryFactory::class,
                Validation::class => InvokableFactory::class,
                'ConfigInputFilterMiddleware' => InputFilterMiddlewareFactory::class,
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
            'subscribers' => [
                Validation::class,
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

    public function getRoutesConfig(): array
    {
        return [
            [
                'path' => '/config/{id:\d+}',
                'middleware' => [
                    TokenMiddleware::class,
                    'DbConfigResourceMiddleware',
                    ValidateOwnerMiddleware::class,
                    GetConfigAction::class,
                ],
                'name' => 'get_config',
                'allowed_methods' => ['GET'],
            ],
            [
                'path' => '/configs',
                'middleware' => [
                    TokenMiddleware::class,
                    SetOwnerFilterMiddleware::class,
                    GetConfigsAction::class,
                ],
                'name' => 'get_configs',
                'allowed_methods' => ['GET'],
            ],
            [
                'path' => '/config',
                'middleware' => [
                    TokenMiddleware::class,
                    BodyParamsMiddleware::class,
                    'ConfigInputFilterMiddleware',
                    SetOwnerMiddleware::class,
                    AddConfigAction::class,
                ],
                'name' => 'add_config',
                'allowed_methods' => ['POST'],
            ],
            [
                'path' => '/config/{id:\d+}',
                'middleware' => [
                    TokenMiddleware::class,
                    'DbConfigResourceMiddleware',
                    ValidateOwnerMiddleware::class,
                    BodyParamsMiddleware::class,
                    'ConfigInputFilterMiddleware',
                    SetOwnerMiddleware::class,
                    UpdateConfigAction::class,
                ],
                'name' => 'update_config',
                'allowed_methods' => ['PUT'],
            ],
            [
                'path' => '/config/{id:\d+}',
                'middleware' => [
                    TokenMiddleware::class,
                    'DbConfigResourceMiddleware',
                    ValidateOwnerMiddleware::class,
                    DeleteConfigAction::class,
                ],
                'name' => 'delete_config',
                'allowed_methods' => ['DELETE'],
            ],
        ];
    }
}
