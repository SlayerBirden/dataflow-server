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
use Zend\Hydrator\ExtractionInterface;

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
     * @var ExtractionInterface
     */
    private $extraction;

    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger,
        ExtractionInterface $extraction
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->extraction = $extraction;
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
        $success = false;
        $msg = null;
        $status = 200;

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
                    return $this->extraction->extract($user);
                }, $users->toArray());
                $success = true;
            } else {
                $msg = new DangerMessage('Could not find users using given conditions.');
                $status = 404;
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $msg = new DangerMessage('Could not fetch users.');
            $status = 400;
        }

        return new JsonResponse([
            'data' => [
                'users' => $arrayUsers ?? [],
                'count' => $count ?? 0,
            ],
            'success' => $success,
            'msg' => $msg,
        ], $status);
    }

    private function buildCriteria(array $filters = [], array $sorting = [], int $page = 1, int $limit = 10): Criteria
    {
        $criteria = Criteria::create();
        $criteria->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        foreach ($filters as $key => $value) {
            $criteria->andWhere(Criteria::expr()->contains($key, $value));
        }
        if ($sorting) {
            $criteria->orderBy($sorting);
        }

        return $criteria;
    }
}
