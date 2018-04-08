<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Service;

use SlayerBirden\DataFlowServer\Authorization\ResourceManagerInterface;

class ResourceManager implements ResourceManagerInterface
{
    /**
     * @inheritdoc
     */
    public function getAllResources(): array
    {
        // todo get actual resources
        return [
            'create_password',
            'tmp_token',
        ];
    }
}
