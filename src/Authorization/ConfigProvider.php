<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization;

use SlayerBirden\DataFlowServer\Authorization\Service\PermissionManager;
use SlayerBirden\DataFlowServer\Authorization\Service\ResourceManager;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            ConfigAbstractFactory::class => [
                PermissionManager::class => [],
            ],
            'doctrine' => [
                'paths' => [
                    'src/Authorization/Entities'
                ],
            ],
            'dependencies' => [
                'aliases' => [
                    PermissionManagerInterface::class => PermissionManager::class,
                    ResourceManagerInterface::class => ResourceManager::class,
                ],
            ],
        ];
    }
}
