<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization;

interface ResourceManagerInterface
{
    /**
     * @return string[]
     */
    public function getAllResources(): array;
}
