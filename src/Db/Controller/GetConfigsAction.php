<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use Zend\Hydrator\HydratorInterface;

final class GetConfigsAction implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var Selectable
     */
    private $dbConfigRepository;

    public function __construct(
        Selectable $dbConfigRepository,
        LoggerInterface $logger,
        HydratorInterface $hydrator
    ) {
        $this->logger = $logger;
        $this->hydrator = $hydrator;
        $this->dbConfigRepository = $dbConfigRepository;
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


        $currentOwner = $request->getAttribute(TokenMiddleware::USER_PARAM);
        $filters['owner'] = $currentOwner;

        try {
            $criteria = $this->buildCriteria($filters, $sorting, $page, $limit);

            $configs = $this->dbConfigRepository->matching($criteria);
            // before collection load to count all records without pagination
            $count = $configs->count();
            if ($count > 0) {
                $arrayConfigs = array_map(function ($config) {
                    return $this->hydrator->extract($config);
                }, $configs->toArray());
                return (new GeneralSuccessResponseFactory())('Success', 'configurations', $arrayConfigs, 200, $count);
            } else {
                $msg = 'Could not find configurations using given conditions.';
                return (new GeneralErrorResponseFactory())($msg, 'configurations', 404, [], 0);
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $msg = 'There was an error while fetching configurations.';
            return (new GeneralErrorResponseFactory())($msg, 'configurations', 400, [], 0);
        }
    }

    private function buildCriteria(
        array $filters = [],
        array $sorting = [],
        int $page = 1,
        int $limit = 10
    ): Criteria {
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
        if (! empty($sorting)) {
            foreach ($sorting as $key => $dir) {
                $criteria->orderBy($sorting);
            }
        }

        return $criteria;
    }
}
