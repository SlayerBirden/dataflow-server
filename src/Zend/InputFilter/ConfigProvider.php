<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Zend\InputFilter;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => [
                'aliases' => [
                    'InputFilterManager' => ImprovedInputFilterPluginManager::class,
                ],
                'factories' => [
                    ImprovedInputFilterPluginManager::class => ImprovedInputFilterPluginManagerFactory::class,
                ],
            ],
        ];
    }
}
