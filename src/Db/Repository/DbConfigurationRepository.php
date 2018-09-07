<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;

final class DbConfigurationRepository implements Selectable
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
        $repo = $this->managerRegistry->getRepository(DbConfiguration::class);

        if ($repo instanceof Selectable) {
            return $repo->matching($criteria);
        }

        throw new \LogicException('DbConfiguration repository does not support "matching"');
    }
}
