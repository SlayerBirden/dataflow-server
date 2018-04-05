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
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

class AddConfigAction implements MiddlewareInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
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
     * @var ExtractionInterface
     */
    private $extractor;

    public function __construct(
        EntityManagerInterface $entityManager,
        HydratorInterface $hydrator,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger,
        ExtractionInterface $extractor
    ) {
        $this->entityManager = $entityManager;
        $this->hydrator = $hydrator;
        $this->inputFilter = $inputFilter;
        $this->logger = $logger;
        $this->extractor = $extractor;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $this->inputFilter->setData($data);

        $message = null;
        $validation = [];
        $created = false;
        $status = 200;

        if ($this->inputFilter->isValid()) {
            try {
                $config = $this->getConfiguration($data);
                $this->entityManager->persist($config);
                $this->entityManager->flush();
                $message = new SuccessMessage('Configuration has been successfully created!');
                $created = true;
            } catch (ORMInvalidArgumentException $exception) {
                $message = new DangerMessage($exception->getMessage());
                $status = 400;
            } catch (ORMException $exception) {
                $this->logger->error((string)$exception);
                $message = new DangerMessage('Error during creation operation.');
                $status = 400;
            }
        } else {
            foreach ($this->inputFilter->getInvalidInput() as $key => $input) {
                $message = new DangerMessage('There were validation errors.');
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
            'success' => $created,
            'data' => [
                'validation' => $validation,
                'configuration' => !empty($config) ? $this->extractor->extract($config) : null,
            ]
        ], $status);
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
