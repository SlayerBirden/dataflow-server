<?php
declare(strict_types=1);

return [
    'dependencies' => [
        'factories' => [
            \SlayerBirden\DataFlowServer\Doctrine\SimpleRegistry::class =>
                \SlayerBirden\DataFlowServer\Doctrine\Factory\ManagerRegistryFactory::class,
            \Doctrine\ORM\Mapping\UnderscoreNamingStrategy::class =>
                \Zend\ServiceManager\Factory\InvokableFactory::class,
        ],
        'aliases' => [
            \SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry::class =>
                \SlayerBirden\DataFlowServer\Doctrine\SimpleRegistry::class,
        ]
    ],
    'doctrine' => [
        'entity_managers' => [
            'default' => [
                /*
                 * Add global project paths here if any
                 * All local paths are defined in modules
                */
                /*
                'paths' => [
                    'src/...'
                ],
                */
                /*
                 * dev_mode is supplied into Setup::createAnnotationMetadataConfiguration
                 */
                'dev_mode' => false,
                'naming_strategy' => \Doctrine\ORM\Mapping\UnderscoreNamingStrategy::class,
                'connection' => 'default',
                'proxy_dir' => 'data/',
            ],
        ],
        /*
         * Explicitly specify which Entities map to which EM
         * Should be defined in modules
         */
        /*
        'entity_manager_mapping' => [
            '\\Some\\Class' => 'some_em',
        ],
        */
    ]
];
