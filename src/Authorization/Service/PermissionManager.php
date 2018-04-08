<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Service;

use SlayerBirden\DataFlowServer\Authorization\PermissionManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class PermissionManager implements PermissionManagerInterface
{
    public function isAllowed(string $resource, User $user): bool
    {
        // TODO: Implement isAllowed() method.
        return true;
    }
}
