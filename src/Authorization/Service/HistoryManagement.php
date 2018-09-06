<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Service;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use SlayerBirden\DataFlowServer\Authorization\Entities\History;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Authorization\HistoryManagementInterface;

final class HistoryManagement implements HistoryManagementInterface
{
    const ACTION_ADD = 'added';
    const ACTION_REMOVE = 'removed';
    const ACTION_UNKNOWN = 'unknown';
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function fromPermission(Permission $permission): History
    {
        $em = $this->managerRegistry->getManagerForClass(Permission::class);
        if ($em === null) {
            throw new \LogicException('Could not obtain ObjectManager');
        }
        if (!($em instanceof EntityManagerInterface)) {
            throw new \LogicException('Could not use ObjectManager');
        }
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
