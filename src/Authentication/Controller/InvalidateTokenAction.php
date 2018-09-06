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
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
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
            return new JsonResponse([
                'msg' => new DangerMessage('Could not retrieve ObjectManager'),
                'success' => false,
                'data' => [
                    'token' => null,
                ]
            ], 500);
        }
        $em->persist($token);
        $em->flush();
        return new JsonResponse([
            'data' => [
                'token' => $this->hydrator->extract($token),
            ],
            'success' => true,
            'msg' => new SuccessMessage('Token invalidated.'),
        ], 200);
    }
}
