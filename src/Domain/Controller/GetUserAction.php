<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use Zend\Hydrator\HydratorInterface;

final class GetUserAction implements MiddlewareInterface
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
        $user = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);
        return (new GeneralSuccessResponseFactory())('Success', 'user', $this->hydrator->extract($user));
    }
}
