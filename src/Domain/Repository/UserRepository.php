<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

final class UserRepository implements Selectable
{
    /**
     * @var EntityManagerRegistry
     */
    private $managerRegistry;

    public function __construct(EntityManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritdoc
     */
    public function matching(Criteria $criteria): Collection
    {
        $repo = $this->managerRegistry->getRepository(User::class);

        if ($repo instanceof Selectable) {
            return $repo->matching($criteria);
        }

        throw new \LogicException('User repository does not support "matching"');
    }
}
