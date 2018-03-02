<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Logger;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    Logger::class => AppLoggerFactory::class,
                ],
                'aliases' => [
                    LoggerInterface::class => Logger::class
                ]
            ],
            'logger' => [
                'handlers' => []
            ]
        ];
    }
}
