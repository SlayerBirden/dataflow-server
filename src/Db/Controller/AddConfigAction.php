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
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

final class AddConfigAction implements MiddlewareInterface
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;
    /**
     * @var LoggerInterface
     */
    private $logger;
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
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('configuration', $this->inputFilter);
        }
        try {
            $config = $this->getConfiguration($data);
            $em = $this->managerRegistry->getManagerForClass(DbConfiguration::class);
            $em->persist($config);
            $em->flush();
            return new JsonResponse([
                'msg' => new SuccessMessage('Configuration has been successfully created!'),
                'success' => true,
                'data' => [
                    'validation' => [],
                    'configuration' => $this->hydrator->extract($config),
                ]
            ], 200);
        } catch (ORMInvalidArgumentException $exception) {
            return new JsonResponse([
                'msg' => new DangerMessage($exception->getMessage()),
                'success' => false,
                'data' => [
                    'validation' => [],
                    'configuration' => null,
                ]
            ], 400);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return new JsonResponse([
                'msg' => new DangerMessage('Error during creation operation.'),
                'success' => false,
                'data' => [
                    'validation' => [],
                    'configuration' => null,
                ]
            ], 500);
        }
    }

    /**
     * @param array $data
     * @return DbConfiguration
     */
    private function getConfiguration(array $data): DbConfiguration
    {
        $config = new DbConfiguration();
        $this->hydrator->hydrate($data, $config);

        return $config;
    }
}
