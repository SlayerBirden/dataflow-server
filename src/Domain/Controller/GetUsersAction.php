<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Controller;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;

class GetUsersAction implements MiddlewareInterface
{
    /**
     * @var EntityManagerInterface
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
        EntityManagerInterface $entityManager,
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
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        $data = $request->getQueryParams();
        $page = isset($data['p']) ? abs($data['p']) : null;
        $filters = $data['f'] ?? [];
        $sorting = $data['s'] ?? [];
        $success = false;
        $msg = '';
        $status = 200;

        try {
            $criteria = $this->buildCriteria($filters, $sorting, $page);

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

    private function buildCriteria(array $filters = [], array $sorting = [], ?int $page, int $limit = 10): Criteria
    {
        $criteria = Criteria::create();
        if ($page !== null) {
            $criteria->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }
        foreach ($filters as $key => $value) {
            $criteria->andWhere(Criteria::expr()->contains($key, $value));
        }
        if ($sorting) {
            $criteria->orderBy($sorting);
        }

        return $criteria;
    }
}
