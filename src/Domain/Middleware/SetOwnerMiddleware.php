<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;

class SetOwnerMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();

        $user = $request->getAttribute(TokenMiddleware::USER_PARAM);
        if ($user) {
            $data[ClaimedResourceInterface::OWNER_PARAM] = $user;
        }

        return $handler->handle($request->withParsedBody($data));
    }
}
