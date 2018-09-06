<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;

final class DeleteConfigAction implements MiddlewareInterface
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

    public function __construct(
        ManagerRegistry $managerRegistry,
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
        $dbConfig = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);

        try {
            $em = $this->managerRegistry->getManagerForClass(DbConfiguration::class);
            $em->remove($dbConfig);
            $em->flush();
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
