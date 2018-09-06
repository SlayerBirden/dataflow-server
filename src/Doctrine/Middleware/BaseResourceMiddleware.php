<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Middleware;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;

final class BaseResourceMiddleware implements ResourceMiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $entityName;
    /**
     * @var string
     */
    private $dataObjectName;
    /**
     * @var string
     */
    private $idAttributeName;
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger,
        string $entityName,
        string $dataObjectName,
        string $idAttributeName = 'id'
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
        $this->entityName = $entityName;
        $this->dataObjectName = $dataObjectName;
        $this->idAttributeName = $idAttributeName;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute($this->idAttributeName);

        if ($id !== null) {
            try {
                $em = $this->managerRegistry->getManagerForClass($this->entityName);
                $entity = $em->find($this->entityName, $id);
                if ($entity) {
                    return $handler->handle(
                        $request->withAttribute(self::DATA_RESOURCE, $entity)
                    );
                } else {
                    return new JsonResponse([
                        'data' => [
                            $this->dataObjectName => null,
                        ],
                        'success' => false,
                        'msg' => new DangerMessage(sprintf('Could not load %s by provided ID.', $this->dataObjectName)),
                    ], 404);
                }
            } catch (ORMInvalidArgumentException $exception) {
                $this->logger->error((string)$exception);
                return new JsonResponse([
                    'data' => [
                        $this->dataObjectName => null,
                    ],
                    'success' => false,
                    'msg' => new DangerMessage(sprintf('Error during loading %s.', $this->dataObjectName)),
                ], 500);
            }
        } else {
            return new JsonResponse([
                'data' => [
                    $this->dataObjectName => null,
                ],
                'success' => false,
                'msg' => new DangerMessage(sprintf('No %s provided.', $this->idAttributeName)),
            ], 400);
        }
    }
}
