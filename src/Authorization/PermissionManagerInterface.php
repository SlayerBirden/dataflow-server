<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization;

use SlayerBirden\DataFlowServer\Domain\Entities\User;

interface PermissionManagerInterface
{
    public function isAllowed(string $resource, string $action, User $user): bool;
}
