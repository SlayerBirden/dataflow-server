<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use SlayerBirden\DataFlowServer\Validation\Exception\ValidationException;
use Zend\Hydrator\HydratorInterface;

final class AddConfigAction implements MiddlewareInterface
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EntityManagerRegistry
     */
    private $managerRegistry;

    public function __construct(
        EntityManagerRegistry $managerRegistry,
        HydratorInterface $hydrator,
        LoggerInterface $logger
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->hydrator = $hydrator;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = Parser::getRequestBody($request);

        try {
            $config = new DbConfiguration();
            $this->hydrator->hydrate($data, $config);
            $em = $this->managerRegistry->getManagerForClass(DbConfiguration::class);
            $em->persist($config);
            $em->flush();
            $msg = 'Configuration has been successfully created!';
            return (new GeneralSuccessResponseFactory())($msg, 'configuration', $this->hydrator->extract($config));
        } catch (ORMInvalidArgumentException | ValidationException $exception) {
            return (new GeneralErrorResponseFactory())($exception->getMessage(), 'configuration', 400);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return (new GeneralErrorResponseFactory())('Error during creation operation.', 'configuration', 400);
        }
    }
}
