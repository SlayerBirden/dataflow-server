<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use SlayerBirden\DataFlowServer\Authorization\Entities\History;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Authorization\HistoryManagementInterface;

class HistoryManagement implements HistoryManagementInterface
{
    const ACTION_ADD = 'added';
    const ACTION_REMOVE = 'removed';
    const ACTION_UNKNOWN = 'unknown';

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function fromPermission(Permission $permission): History
    {
        $state = $this->entityManager->getUnitOfWork()->getEntityState($permission);

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
