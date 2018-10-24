<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\NotFound;

use Zend\ServiceManager\Factory\InvokableFactory;

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
                    JsonNotFoundHandler::class => InvokableFactory::class,
                ],
            ],
        ];
    }
}
