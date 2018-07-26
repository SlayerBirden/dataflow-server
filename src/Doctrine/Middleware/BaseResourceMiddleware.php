<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Middleware;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;

class BaseResourceMiddleware implements ResourceMiddlewareInterface
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

    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger,
        string $entityName,
        string $dataObjectName,
        string $idAttributeName = 'id'
    ) {
        $this->entityManager = $entityManager;
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
                $entity = $this->entityManager->find($this->entityName, $id);
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
            } catch (ORMInvalidArgumentException | ORMException $exception) {
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
