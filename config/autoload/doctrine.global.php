<?php
declare(strict_types=1);

return [
    'dependencies' => [
        'factories'  => [
            \Doctrine\ORM\EntityManager::class => \SlayerBirden\DataFlowServer\Doctrine\EntityManagerFactory::class
        ],
        'aliases' => [
            \Doctrine\ORM\EntityManagerInterface::class => \Doctrine\ORM\EntityManager::class
        ]
    ],
    'doctrine' => [
        /* Add global project paths here if any */
        /*
        'paths' => [
            'src/...'
        ],
        */
        'dev_mode' => false,
        /**
         * Can resolve services as argument names if applicable
         */
        'configuration' => []
    ]
];
