<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\NotFound;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;

final class JsonNotFoundHandler implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return (new GeneralErrorResponseFactory())("Not Found", null, 404);
    }
}
