<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
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
use Zend\Hydrator\ExtractionInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

class UpdateConfigAction implements MiddlewareInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ExtractionInterface
     */
    private $extraction;
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

    public function __construct(
        EntityManagerInterface $entityManager,
        HydratorInterface $hydrator,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger,
        ExtractionInterface $extraction
    ) {
        $this->entityManager = $entityManager;
        $this->hydrator = $hydrator;
        $this->inputFilter = $inputFilter;
        $this->logger = $logger;
        $this->extraction = $extraction;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $dbConfig = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);
        if (!$dbConfig) {
            return new JsonResponse([
                'msg' => new DangerMessage('Could not find Configuration.'),
                'success' => false,
                'data' => [
                    'configuration' => null,
                ]
            ], 404);
        }

        $this->inputFilter->setData($data);

        $message = null;
        $validation = [];
        $updated = false;
        $status = 200;

        if ($this->inputFilter->isValid()) {
            try {
                $config = $this->getConfig($dbConfig, $data);
                $this->entityManager->persist($config);
                $this->entityManager->flush();
                $message = new SuccessMessage('Configuration has been updated!');
                $updated = true;
            } catch (ORMInvalidArgumentException $exception) {
                $message = new DangerMessage($exception->getMessage());
                $status = 400;
            } catch (ORMException $exception) {
                $this->logger->error((string)$exception);
                $message = new DangerMessage('Error while updating configuration.');
                $status = 400;
            }
        } else {
            $message = new DangerMessage('There were validation errors.');
            foreach ($this->inputFilter->getInvalidInput() as $key => $input) {
                $messages = $input->getMessages();
                $validation[] = [
                    'field' => $key,
                    'msg' => reset($messages)
                ];
            }
            $status = 400;
        }

        return new JsonResponse([
            'msg' => $message,
            'success' => $updated,
            'data' => [
                'configuration' => !empty($config) ? $this->extraction->extract($config) : null,
                'validation' => $validation,
            ]
        ], $status);
    }

    private function getConfig(DbConfiguration $oldConfig, array $data): DbConfiguration
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }
        $this->hydrator->hydrate($data, $oldConfig);

        return $oldConfig;
    }
}
