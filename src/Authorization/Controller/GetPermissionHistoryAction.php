<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Controller;

use Doctrine\Common\Collections\Selectable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Collection\CriteriaBuilder;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\ListExtractor;
use SlayerBirden\DataFlowServer\Stdlib\ResponseFactory;
use Zend\Hydrator\HydratorInterface;

final class GetPermissionHistoryAction implements MiddlewareInterface
{
    /**
     * @var Selectable
     */
    private $historyRepository;
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(Selectable $historyRepository, HydratorInterface $hydrator)
    {
        $this->historyRepository = $historyRepository;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $collection = $this->historyRepository->matching((new CriteriaBuilder())($request->getQueryParams()));

        $results = (new ListExtractor())($this->hydrator, $collection->toArray());
        $count = count($results);

        return (new ResponseFactory())('Success', 200, 'history', $results, $count);
    }
}
