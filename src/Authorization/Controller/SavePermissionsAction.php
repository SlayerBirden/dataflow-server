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
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
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
            return new JsonResponse([
                'msg' => new DangerMessage('Could not retrieve ObjectManager'),
                'success' => false,
                'data' => [
                    'token' => null,
                ]
            ], 500);
        }
        if (!($em instanceof EntityManagerInterface)) {
            return new JsonResponse([
                'msg' => new DangerMessage('Can not use current ObjectManager'),
                'success' => false,
                'data' => [
                    'token' => null,
                ]
            ], 500);
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

            return new JsonResponse([
                'msg' => $msg,
                'data' => [
                    'permissions' => array_map([$this->hydrator, 'extract'], $permissions),
                ],
                'success' => true,
            ], 200);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $em->rollback();

            return new JsonResponse([
                'msg' => new DangerMessage('There was an error while setting the permissions.'),
                'data' => [
                    'permissions' => [],
                ],
                'success' => false,
            ], 400);
        }
    }

    /**
     * @param User $user
     * @param User $owner
     * @param string ...$resources
     * @return Permission[]
     * @throws ORMException
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
     * @throws ORMException
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
