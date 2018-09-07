<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;

final class PasswordRepository implements Selectable
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
        $repo = $this->managerRegistry->getRepository(Password::class);

        if ($repo instanceof Selectable) {
            return $repo->matching($criteria);
        }

        throw new \LogicException('Password repository does not support "matching"');
    }
}
