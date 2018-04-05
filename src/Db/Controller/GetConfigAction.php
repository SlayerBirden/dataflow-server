<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;

class GetConfigAction implements MiddlewareInterface
{
    /**
     * @var ExtractionInterface
     */
    private $extraction;

    public function __construct(
        ExtractionInterface $extraction
    ) {
        $this->extraction = $extraction;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $dbConfig = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);

        if (!$dbConfig) {
            return new JsonResponse([
                'data' => [
                    'configuration' => null,
                ],
                'success' => false,
                'msg' => new DangerMessage('Could not load DB Configuration.'),
            ], 400);
        }

        return new JsonResponse([
            'data' => [
                'configuration' => $this->extraction->extract($dbConfig),
            ],
            'success' => true,
            'msg' => null,
        ], 200);
    }
}
