<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;

final class GetConfigAction implements MiddlewareInterface
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(
        HydratorInterface $hydrator
    ) {
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $dbConfig = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);

        return new JsonResponse([
            'data' => [
                'configuration' => $this->hydrator->extract($dbConfig),
            ],
            'success' => true,
            'msg' => null,
        ], 200);
    }
}
