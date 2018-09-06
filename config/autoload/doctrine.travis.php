<?php
declare(strict_types=1);

return [
    'doctrine' => [
        'entity_managers' => [
            'default' => [
                'dev_mode' => true,
                'connection' => 'default',
                'proxy_dir' => 'data/',
            ],
        ],
    ]
];
