<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

final class UpdateConfigAction implements MiddlewareInterface
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
     * @var InputFilterInterface
     */
    private $inputFilter;
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(
        ManagerRegistry $managerRegistry,
        HydratorInterface $hydrator,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->hydrator = $hydrator;
        $this->inputFilter = $inputFilter;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return (new DataValidationResponseFactory())('configuration');
        }
        $dbConfig = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('configuration', $this->inputFilter);
        }
        try {
            $config = $this->getConfig($dbConfig, $data);
            $em = $this->managerRegistry->getManagerForClass(DbConfiguration::class);
            $em->persist($config);
            $em->flush();
            $msg = 'Configuration has been updated!';
            return (new GeneralSuccessResponseFactory())($msg, 'configuration', $this->hydrator->extract($config));
        } catch (ORMInvalidArgumentException $exception) {
            return (new GeneralErrorResponseFactory())($exception->getMessage(), 'configuration', 400);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return (new GeneralErrorResponseFactory())('Error while updating configuration.', 'configuration', 400);
        }
    }

    private function getConfig(DbConfiguration $configuration, array $data): DbConfiguration
    {
        unset($data['id']);
        $this->hydrator->hydrate($data, $configuration);

        return $configuration;
    }
}
