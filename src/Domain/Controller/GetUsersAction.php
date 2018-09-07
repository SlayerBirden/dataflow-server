<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use Zend\Hydrator\HydratorInterface;

final class GetUsersAction implements MiddlewareInterface
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
    private $userRepository;

    public function __construct(
        Selectable $userRepository,
        LoggerInterface $logger,
        HydratorInterface $hydrator
    ) {
        $this->userRepository = $userRepository;
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

            $users = $this->userRepository->matching($criteria);
            // before collection load to count all records without pagination
            $count = $users->count();

            if ($count > 0) {
                $arrayUsers = array_map(function ($user) {
                    return $this->hydrator->extract($user);
                }, $users->toArray());
                return (new GeneralSuccessResponseFactory())('Success', 'users', $arrayUsers, 200, $count);
            } else {
                $msg = 'Could not find users using given conditions.';
                return (new GeneralErrorResponseFactory())($msg, 'users', 404, [], 0);
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $msg = 'There was an error while fetching users.';
            return (new GeneralErrorResponseFactory())($msg, 'users', 400, [], 0);
        } catch (\Exception $exception) {
            $this->logger->error((string)$exception);
            return (new GeneralErrorResponseFactory())('Internal error', 'users', 500, [], 0);
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
        if (! empty($sorting)) {
            foreach ($sorting as $key => $dir) {
                $criteria->orderBy($sorting);
            }
        }

        return $criteria;
    }
}
