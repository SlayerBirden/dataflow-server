<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

class AddUserAction implements MiddlewareInterface
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
    private $extraction;

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
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();

        $this->inputFilter->setData($data);

        $message = null;
        $validation = [];
        $created = false;
        $status = 200;

        if ($this->inputFilter->isValid()) {
            try {
                $entity = $this->getEntity($data);
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                $message = new SuccessMessage('User has been successfully created!');
                $created = true;
            } catch (ORMInvalidArgumentException $exception) {
                $message = new DangerMessage($exception->getMessage());
                $status = 400;
            } catch (UniqueConstraintViolationException $exception) {
                $message = new DangerMessage('Provided email already exists.');
                $status = 400;
            } catch (ORMException $exception) {
                $this->logger->error((string)$exception);
                $message = new DangerMessage('Error during creation operation.');
                $status = 400;
            }
        } else {
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
            'success' => $created,
            'data' => [
                'validation' => $validation,
                'user' => isset($entity) ? $this->extraction->extract($entity) : null,
            ]
        ], $status);
    }

    /**
     * @param array $data
     * @return User
     */
    private function getEntity(array $data): User
    {
        $entity = new User();
        $this->hydrator->hydrate($data, $entity);

        return $entity;
    }
}
