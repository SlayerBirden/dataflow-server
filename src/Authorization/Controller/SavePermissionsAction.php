<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Authorization\HistoryManagementInterface;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\ListExtractor;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;
use SlayerBirden\DataFlowServer\Stdlib\ResponseFactory;
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
     * @var EntityManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var Selectable
     */
    private $permissionRepository;

    public function __construct(
        EntityManagerRegistry $managerRegistry,
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
     * @throws ORMException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = Parser::getRequestBody($request);
        $user = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);

        $this->inputFilter->setData($data);
        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('permissions', $this->inputFilter, []);
        }
        $em = $this->managerRegistry->getManagerForClass(Permission::class);
        $em->beginTransaction();
        try {
            $permissions = $this->processResources(
                $user,
                $data[ClaimedResourceInterface::OWNER_PARAM],
                ...$data['resources']
            );
            $em->flush();
            $em->commit();
            $msg = 'Successfully set permissions to resources.';
            $extractedPermissions = (new ListExtractor())($this->hydrator, $permissions);
            $count = count($extractedPermissions);
            return (new ResponseFactory())($msg, 200, 'permissions', $extractedPermissions, $count);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $em->rollback();
            $msg = 'There was an error while setting the permissions.';
            return (new ResponseFactory())($msg, 400, 'permissions', [], 0);
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
     * @throws ORMException
     */
    private function processItemsToRemove($collection, $toRemove, $owner, &$result): void
    {
        $em = $this->managerRegistry->getManagerForClass(Permission::class);
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
