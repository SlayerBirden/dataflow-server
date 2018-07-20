<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Controller;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;

class GetUsersAction implements MiddlewareInterface
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
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger,
        HydratorInterface $hydrator
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getQueryParams();
        $page = isset($data['p']) ? abs($data['p']) : 1;
        $limit = isset($data['l']) ? abs($data['l']) : 10;
        $filters = $data['f'] ?? [];
        $sorting = $data['s'] ?? [];

        try {
            $criteria = $this->buildCriteria($filters, $sorting, $page, $limit);

            /** @var Collection $users */
            $users = $this->entityManager
                ->getRepository(User::class)
                ->matching($criteria);
            // before collection load to count all records without pagination
            $count = $users->count();

            if ($count > 0) {
                $arrayUsers = array_map(function ($user) {
                    return $this->hydrator->extract($user);
                }, $users->toArray());
                return new JsonResponse([
                    'data' => [
                        'users' => $arrayUsers,
                        'count' => $count,
                    ],
                    'success' => true,
                    'msg' => null,
                ], 200);
            } else {
                return new JsonResponse([
                    'data' => [
                        'users' => [],
                        'count' => 0,
                    ],
                    'success' => false,
                    'msg' => new DangerMessage('Could not find users using given conditions.'),
                ], 404);
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return new JsonResponse([
                'data' => [
                    'users' => [],
                    'count' => 0,
                ],
                'success' => false,
                'msg' => new DangerMessage('There was an error while fetching users.'),
            ], 400);
        }
    }

    private function buildCriteria(array $filters = [], array $sorting = [], int $page = 1, int $limit = 10): Criteria
    {
        $criteria = Criteria::create();
        $criteria->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        foreach ($filters as $key => $value) {
            if (is_string($value)) {
                $criteria->andWhere(Criteria::expr()->contains($key, $value));
            } else {
                $criteria->andWhere(Criteria::expr()->eq($key, $value));
            }
        }
        if ($sorting) {
            foreach ($sorting as $key => $dir) {
                $criteria->orderBy($sorting);
            }
        }

        return $criteria;
    }
}
