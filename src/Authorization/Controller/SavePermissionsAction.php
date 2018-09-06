<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Authorization\HistoryManagementInterface;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

final class SavePermissionsAction implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;
    /**
     * @var HistoryManagementInterface
     */
    private $historyManagement;
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var Selectable
     */
    private $permissionRepository;

    public function __construct(
        ManagerRegistry $managerRegistry,
        Selectable $permissionRepository,
        LoggerInterface $logger,
        InputFilterInterface $inputFilter,
        HistoryManagementInterface $historyManagement,
        HydratorInterface $hydrator
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->permissionRepository = $permissionRepository;
        $this->logger = $logger;
        $this->inputFilter = $inputFilter;
        $this->historyManagement = $historyManagement;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return (new DataValidationResponseFactory())('permissions', []);
        }
        $user = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);

        $this->inputFilter->setData($data);
        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('permissions', $this->inputFilter, []);
        }
        $em = $this->managerRegistry->getManagerForClass(Permission::class);
        if ($em === null) {
            return (new GeneralErrorResponseFactory())('Could not retrieve ObjectManager', 'permissions', 500, [], 0);
        }
        if (!($em instanceof EntityManagerInterface)) {
            return (new GeneralErrorResponseFactory())('Can not use current ObjectManager', 'permissions', 500, [], 0);
        }
        $em->beginTransaction();
        try {
            $permissions = $this->processResources(
                $user,
                $data[ClaimedResourceInterface::OWNER_PARAM],
                ...$data['resources']
            );
            if (empty($permissions)) {
                $msg = new SuccessMessage('No changes detected. The input is identical to the storage.');
            } else {
                $em->flush();
                $em->commit();
                $msg = new SuccessMessage('Successfully set permissions to resources.');
            }

            $extractedPermissions = array_map([$this->hydrator, 'extract'], $permissions);
            $count = count($extractedPermissions);
            return (new GeneralSuccessResponseFactory())($msg, 'permissions', $extractedPermissions, 200, $count);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $em->rollback();

            $msg = 'There was an error while setting the permissions.';
            return (new GeneralErrorResponseFactory())($msg, 'permissions', 400, [], 0);
        }
    }

    /**
     * @param User $user
     * @param User $owner
     * @param string ...$resources
     * @return Permission[]
     */
    private function processResources(User $user, User $owner, string ...$resources): array
    {
        $result = [];
        $collection = $this->permissionRepository->matching(
            Criteria::create()->where(Criteria::expr()->eq('user', $user))
        );

        $currentResources = array_map(function (Permission $permission) {
            return $permission->getResource();
        }, $collection->toArray());

        $toRemove = array_diff($currentResources, $resources);
        $toAdd = array_diff($resources, $currentResources);

        if (empty($toAdd) && empty($toRemove)) {
            return [];
        }

        $this->processItemsToRemove($collection, $toRemove, $owner, $result);
        $this->processItemsToAdd($toAdd, $user, $owner, $result);

        return $result;
    }

    /**
     * @param $collection
     * @param $toRemove
     * @param $owner
     * @param $result
     */
    private function processItemsToRemove($collection, $toRemove, $owner, &$result): void
    {
        $em = $this->managerRegistry->getManagerForClass(Permission::class);
        if ($em === null) {
            throw new \LogicException('Could not obtain ObjectManager');
        }
        /** @var Permission $permission */
        foreach ($collection as $permission) {
            if (in_array($permission->getResource(), $toRemove, true)) {
                $em->remove($permission);
                $history = $this->historyManagement->fromPermission($permission);
                $history->setOwner($owner);
                $em->persist($history);
            } else {
                $result[] = $permission;
            }
        }
    }

    /**
     * @param $toAdd
     * @param $user
     * @param $owner
     * @param $result
     */
    private function processItemsToAdd($toAdd, $user, $owner, &$result): void
    {
        $em = $this->managerRegistry->getManagerForClass(Permission::class);
        if ($em === null) {
            throw new \LogicException('Could not obtain ObjectManager');
        }
        foreach ($toAdd as $resource) {
            $permission = new Permission();
            $permission->setResource($resource);
            $permission->setUser($user);
            $result[] = $permission;
            $em->persist($permission);
            $history = $this->historyManagement->fromPermission($permission);
            $history->setOwner($owner);
            $em->persist($history);
        }
    }
}
