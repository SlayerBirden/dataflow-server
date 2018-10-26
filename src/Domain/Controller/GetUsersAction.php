<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Controller;

use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Collection\CriteriaBuilder;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\ListExtractor;
use SlayerBirden\DataFlowServer\Stdlib\ResponseFactory;
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
        try {
            $users = $this->userRepository->matching((new CriteriaBuilder())($request->getQueryParams()));
            // before collection load to count all records without pagination
            $count = $users->count();

            if ($count > 0) {
                $arrayUsers = (new ListExtractor())($this->hydrator, $users->toArray());
                return (new ResponseFactory())('Success', 200, 'users', $arrayUsers, $count);
            } else {
                $msg = 'Could not find users using given conditions.';
                return (new ResponseFactory())($msg, 404, 'users', [], 0);
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $msg = 'There was an error while fetching users.';
            return (new ResponseFactory())($msg, 400, 'users', [], 0);
        }
    }
}
