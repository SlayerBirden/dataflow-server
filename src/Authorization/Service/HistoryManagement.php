<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Service;

use Doctrine\ORM\UnitOfWork;
use SlayerBirden\DataFlowServer\Authorization\Entities\History;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Authorization\HistoryManagementInterface;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;

final class HistoryManagement implements HistoryManagementInterface
{
    const ACTION_ADD = 'added';
    const ACTION_REMOVE = 'removed';
    const ACTION_UNKNOWN = 'unknown';
    /**
     * @var EntityManagerRegistry
     */
    private $managerRegistry;

    public function __construct(EntityManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param Permission $permission
     * @return History
     * @throws \Doctrine\ORM\ORMException
     */
    public function fromPermission(Permission $permission): History
    {
        $em = $this->managerRegistry->getManagerForClass(Permission::class);
        $state = $em->getUnitOfWork()->getEntityState($permission);

        switch ($state) {
            case UnitOfWork::STATE_MANAGED:
                $action = self::ACTION_ADD;
                break;
            case UnitOfWork::STATE_REMOVED:
                $action = self::ACTION_REMOVE;
                break;
            default:
                $action = self::ACTION_UNKNOWN;
        }

        $history = new History();
        $history->setChangeAction($action);
        $history->setPermission($permission);
        $history->setResource($permission->getResource());
        $history->setUser($permission->getUser());
        $history->setAt(new \DateTime());

        return $history;
    }
}
