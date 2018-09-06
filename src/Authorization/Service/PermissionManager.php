<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use SlayerBirden\DataFlowServer\Authorization\PermissionManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

final class PermissionManager implements PermissionManagerInterface
{
    /**
     * @var Selectable
     */
    private $permissionRepository;

    public function __construct(Selectable $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function isAllowed(string $resource, User $user): bool
    {
        $collection = $this->permissionRepository->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('user', $user))
                ->andWhere(Criteria::expr()->eq('resource', $resource))
        );

        return !$collection->isEmpty();
    }
}
