<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            'doctrine' => [
                'paths' => [
                    'src/Domain/Entities'
                ],
            ],
        ];
    }
}
