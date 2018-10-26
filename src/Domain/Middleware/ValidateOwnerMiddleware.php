<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ResponseFactory;

final class ValidateOwnerMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $resource = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);

        if ($resource && $resource instanceof ClaimedResourceInterface) {
            $resourceOwner = $resource->getOwner();
            /** @var User|null $currentOwner */
            $currentOwner = $request->getAttribute(TokenMiddleware::USER_PARAM);

            if (!$currentOwner || ($currentOwner->getId() !== $resourceOwner->getId())) {
                return (new ResponseFactory())('Access denied.', 403);
            }
        }

        return $handler->handle($request);
    }
}
