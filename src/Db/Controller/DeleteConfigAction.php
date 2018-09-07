<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
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
        $dbConfig = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);

        try {
            $em = $this->managerRegistry->getManagerForClass(DbConfiguration::class);
            $em->remove($dbConfig);
            $em->flush();
            $msg = 'Configuration removed.';
            return (new GeneralSuccessResponseFactory())($msg, 'configuration', $this->hydrator->extract($dbConfig));
        } catch (ORMException | \Exception $exception) {
            $this->logger->error((string)$exception);
            $msg = 'There was an error while removing configuration.';
            return (new GeneralErrorResponseFactory())($msg, 'configuration');
        }
    }
}
