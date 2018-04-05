<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;

class GetConfigsAction implements MiddlewareInterface
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
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getQueryParams();
        $page = isset($data['p']) ? abs($data['p']) : null;
        $filters = $data['f'] ?? [];
        $sorting = $data['s'] ?? [];
        $success = false;
        $msg = null;
        $status = 200;

        try {
            $criteria = $this->buildCriteria($filters, $sorting, $page);

            /** @var Collection $configs */
            $configs = $this->entityManager
                ->getRepository(DbConfiguration::class)
                ->matching($criteria);
            // before collection load to count all records without pagination
            $count = $configs->count();
            if ($count > 0) {
                $arrayConfigs = array_map(function ($user) {
                    return $this->extraction->extract($user);
                }, $configs->toArray());
                $success = true;
            } else {
                $msg = new DangerMessage('Could not find configurations using given conditions.');
                $status = 404;
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $msg = new DangerMessage('Could not fetch configs.');
            $status = 400;
        }

        return new JsonResponse([
            'data' => [
                'configurations' => $arrayConfigs ?? [],
                'count' => $count ?? 0,
            ],
            'success' => $success,
            'msg' => $msg,
        ], $status);
    }

    private function buildCriteria(array $filters = [], array $sorting = [], ?int $page, int $limit = 10): Criteria
    {
        // todo add filter for current user
        $criteria = Criteria::create();
        if ($page !== null) {
            $criteria->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }
        foreach ($filters as $key => $value) {
            $criteria->andWhere(Criteria::expr()->contains($key, $value));
        }
        if ($sorting) {
            foreach ($sorting as $key => $dir) {
                $criteria->orderBy($sorting);
            }
        }

        return $criteria;
    }
}
