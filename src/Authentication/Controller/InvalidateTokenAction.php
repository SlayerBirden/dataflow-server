<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use Zend\Hydrator\HydratorInterface;

final class InvalidateTokenAction implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var EntityManagerRegistry
     */
    private $managerRegistry;

    public function __construct(
        EntityManagerRegistry $managerRegistry,
        LoggerInterface $logger,
        HydratorInterface $hydrator
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Token $token */
        $token = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);
        $token->setActive(false);

        try {
            $em = $this->managerRegistry->getManagerForClass(get_class($token));
            $em->persist($token);
            $em->flush();
            $msg = 'Token invalidated.';
            return (new GeneralSuccessResponseFactory())($msg, 'token', $this->hydrator->extract($token));
        } catch (\Exception $exception) {
            $this->logger->error((string)$exception);
            $msg = 'There was an error during token invalidation';
            return (new GeneralErrorResponseFactory())($msg, 'token');
        }
    }
}
