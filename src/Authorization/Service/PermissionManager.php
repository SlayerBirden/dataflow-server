<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Authorization\PermissionManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class PermissionManager implements PermissionManagerInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function isAllowed(string $resource, User $user): bool
    {
        $collection = $this->entityManager
            ->getRepository(Permission::class)
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->eq('user', $user))
                    ->andWhere(Criteria::expr()->eq('resource', $resource))
            );

        return !$collection->isEmpty();
    }
}
