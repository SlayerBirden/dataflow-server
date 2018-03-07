<?php
declare(strict_types=1);

use Monolog\Handler\NoopHandler;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'debug' => true,
    ConfigAggregator::ENABLE_CACHE => true,
    'logger' => [
        'handlers' => [
            NoopHandler::class
        ],
    ],
    'dependencies' => [
        'factories' => [
            NoopHandler::class => InvokableFactory::class
        ],
    ],
];
