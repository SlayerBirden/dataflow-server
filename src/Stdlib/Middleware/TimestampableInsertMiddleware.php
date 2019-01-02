<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Stdlib\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;

final class TimestampableInsertMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = Parser::getRequestBody($request);

        $data['created_at'] = (new \DateTime('now'))->format(DATE_RFC3339);
        $data['updated_at'] = (new \DateTime('now'))->format(DATE_RFC3339);

        return $handler->handle($request->withParsedBody($data));
    }
}
