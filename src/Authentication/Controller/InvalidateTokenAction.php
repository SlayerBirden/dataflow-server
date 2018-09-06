<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
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
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger, HydratorInterface $hydrator)
    {
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

        $em = $this->managerRegistry->getManagerForClass(get_class($token));
        if ($em === null) {
            return (new GeneralErrorResponseFactory())('Could not retrieve ObjectManager', 'token');
        }
        $em->persist($token);
        $em->flush();
        return (new GeneralSuccessResponseFactory())('Token invalidated.', 'token', $this->hydrator->extract($token));
    }
}
