<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Collection\CriteriaBuilder;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\ListExtractor;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ResponseFactory;
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

        try {
            $configs = $this->dbConfigRepository->matching((new CriteriaBuilder())($data));
            // before collection load to count all records without pagination
            $count = $configs->count();
            if ($count > 0) {
                $arrayConfigs = (new ListExtractor())($this->hydrator, $configs->toArray());
                return (new ResponseFactory())('Success', 200, 'configurations', $arrayConfigs, $count);
            } else {
                $msg = 'Could not find configurations using given conditions.';
                return (new ResponseFactory())($msg, 404, 'configurations', [], 0);
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $msg = 'There was an error while fetching configurations.';
            return (new ResponseFactory())($msg, 400, 'configurations', [], 0);
        }
    }
}
