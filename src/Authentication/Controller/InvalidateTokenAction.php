<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;

class InvalidateTokenAction implements MiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger, HydratorInterface $hydrator)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     * @throws ORMException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Token $token */
        $token = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);
        $token->setActive(false);

        $this->entityManager->persist($token);
        $this->entityManager->flush();
        return new JsonResponse([
            'data' => [
                'token' => $this->hydrator->extract($token),
            ],
            'success' => true,
            'msg' => new SuccessMessage('Token invalidated.'),
        ], 200);
    }
}
