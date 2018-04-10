<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Authorization\HistoryManagementInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;
use Zend\InputFilter\InputFilterInterface;

class SavePermissionsAction implements MiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;
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
     * @var ExtractionInterface
     */
    private $extraction;

    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger,
        InputFilterInterface $inputFilter,
        HistoryManagementInterface $historyManagement,
        ExtractionInterface $extraction
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->inputFilter = $inputFilter;
        $this->historyManagement = $historyManagement;
        $this->extraction = $extraction;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $userId = (int)$request->getAttribute('id');

        $resources = $data['resources'] ?? [];
        $owner = $data['owner'] ?? null;

        if ($userId === null) {
            return new JsonResponse([
                'msg' => new DangerMessage('No user provided.'),
                'data' => [
                    'permissions' => []
                ],
                'success' => false,
            ], 400);
        }

        if ($owner === null) {
            return new JsonResponse([
                'msg' => new DangerMessage('Empty owner (no context).'),
                'data' => [
                    'permissions' => []
                ],
                'success' => false,
            ], 400);
        }

        if (empty($resources)) {
            return new JsonResponse([
                'msg' => new DangerMessage('No Resources provided.'),
                'data' => [
                    'permissions' => []
                ],
                'success' => false,
            ], 400);
        }

        $this->entityManager->beginTransaction();
        try {
            $permissions = $this->processResources($this->getUser($userId), $owner, ...$resources);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse([
                'msg' => new SuccessMessage('Successfully set permissions to resources.'),
                'data' => [
                    'permissions' => array_map([$this->extraction, 'extract'], $permissions),
                ],
                'success' => true,
            ], 200);
        } catch (\InvalidArgumentException $exception) {
            $this->entityManager->rollback();

            return new JsonResponse([
                'msg' => new DangerMessage($exception->getMessage()),
                'data' => [
                    'permissions' => [],
                ],
                'success' => false,
            ], 400);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $this->entityManager->rollback();

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
     * @param string[] ...$resources
     * @return Permission[]
     * @throws ORMException
     */
    private function processResources(User $user, User $owner, string ...$resources): array
    {
        $result = [];
        $collection = $this->entityManager
            ->getRepository(Permission::class)
            ->matching(Criteria::create()->where(Criteria::expr()->eq('user', $user)));

        $currentResources = array_map(function (Permission $permission) {
            return $permission->getResource();
        }, $collection->toArray());

        $toRemove = array_diff($currentResources, $resources);
        $toAdd = array_diff($resources, $currentResources);

        if (empty($toAdd) && empty($toRemove)) {
            throw new \InvalidArgumentException("No changes detected. The input is identical to the storage.");
        }

        /** @var Permission $permission */
        foreach ($collection as $permission) {
            if (in_array($permission->getResource(), $toRemove, true)) {
                $this->entityManager->remove($permission);
                $history = $this->historyManagement->fromPermission($permission);
                $history->setOwner($owner);
                $this->entityManager->persist($history);

            } else {
                $result[] = $permission;
            }
        }
        foreach ($toAdd as $resource) {
            $permission = new Permission();
            $permission->setResource($resource);
            $permission->setUser($user);
            $result[] = $permission;
            $this->entityManager->persist($permission);
            $history = $this->historyManagement->fromPermission($permission);
            $history->setOwner($owner);
            $this->entityManager->persist($history);
        }

        return $result;
    }

    /**
     * @param int $userId
     * @return User
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \InvalidArgumentException
     */
    private function getUser(int $userId): User
    {
        /** @var User $user */
        $user = $this->entityManager->find(User::class, $userId);

        if (! $user) {
            throw new \InvalidArgumentException('Could not find user by provided ID.');
        }

        return $user;
    }
}
