<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Authorization\Service\ResourceManager;
use Zend\Diactoros\Response\JsonResponse;

final class GetResourcesAction implements MiddlewareInterface
{
    /**
     * @var ResourceManager
     */
    private $resourceManager;

    public function __construct(ResourceManager $resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new JsonResponse([
            'data' => [
                'resources' => $this->resourceManager->getAllResources(),
            ],
            'success' => true,
            'msg' => null,
        ], 200);
    }
}
