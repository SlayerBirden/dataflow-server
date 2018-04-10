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
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;

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
     * @var ExtractionInterface
     */
    private $extraction;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger, ExtractionInterface $extraction)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->extraction = $extraction;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);
        if (!$token) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('Could not find token by provided id.'),
            ], 404);
        }
        try {
            $token->setActive(false);

            $this->entityManager->persist($token);
            $this->entityManager->flush();
            return new JsonResponse([
                'data' => [
                    'token' => $this->extraction->extract($token),
                ],
                'success' => true,
                'msg' => new SuccessMessage('Token invalidated.'),
            ], 200);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('There was an error while invalidating token.'),
            ], 400);
        }
    }
}
