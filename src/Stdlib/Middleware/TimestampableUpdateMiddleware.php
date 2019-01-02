<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Stdlib\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;

final class TimestampableUpdateMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = Parser::getRequestBody($request);

        if (isset($data['created_at'])) {
            unset($data['created_at']);
        }
        $data['updated_at'] = (new \DateTime('now'))->format(DATE_RFC3339);

        return $handler->handle($request->withParsedBody($data));
    }
}
