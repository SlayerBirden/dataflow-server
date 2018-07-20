<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\ORM\EntityManagerInterface;
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
use Zend\Hydrator\HydratorInterface;

class DeleteConfigAction implements MiddlewareInterface
{
    /**
     * @var EntityManagerInterface
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

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        HydratorInterface $hydrator
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $dbConfig = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);

        try {
            $this->entityManager->remove($dbConfig);
            $this->entityManager->flush();
            return new JsonResponse([
                'msg' => new SuccessMessage('Configuration removed.'),
                'success' => true,
                'data' => [
                    'configuration' => $this->hydrator->extract($dbConfig),
                ],
            ], 200);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return new JsonResponse([
                'msg' => new DangerMessage('There was an error while removing configuration.'),
                'success' => false,
                'data' => [
                    'configuration' => null
                ],
            ], 500);
        }
    }
}
