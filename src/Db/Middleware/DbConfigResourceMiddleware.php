<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Middleware;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;

class DbConfigResourceMiddleware implements ResourceMiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id');
        $msg = new DangerMessage('Could not load Configuration by provided ID.');

        if ($id !== null) {
            try {
                $dbConfig = $this->entityManager->find(DbConfiguration::class, $id);
                if ($dbConfig) {
                    return $handler->handle(
                        $request->withAttribute(self::DATA_RESOURCE, $dbConfig)
                    );
                }
            } catch (ORMInvalidArgumentException | ORMException $exception) {
                $this->logger->error((string)$exception);
                $msg = new DangerMessage('Error during loading DB Configuration.');
            }
        }

        return new JsonResponse([
            'data' => [
                'configuration' => null,
            ],
            'success' => false,
            'msg' => $msg,
        ], 404);
    }
}
