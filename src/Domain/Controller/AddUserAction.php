<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
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

    public function __construct(
        EntityManagerInterface $entityManager,
        HydratorInterface $hydrator,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
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
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('user', $this->inputFilter);
        }
        try {
            $entity = $this->getEntity($data);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            return new JsonResponse([
                'msg' => new SuccessMessage('User has been successfully created!'),
                'success' => true,
                'data' => [
                    'validation' => [],
                    'user' => $this->hydrator->extract($entity),
                ]
            ], 200);
        } catch (ORMInvalidArgumentException $exception) {
            return new JsonResponse([
                'msg' => new DangerMessage($exception->getMessage()),
                'success' => false,
                'data' => [
                    'validation' => [],
                    'user' => null,
                ]
            ], 400);
        } catch (UniqueConstraintViolationException $exception) {
            return new JsonResponse([
                'msg' => new DangerMessage('Provided email already exists.'),
                'success' => false,
                'data' => [
                    'validation' => [],
                    'user' => null,
                ]
            ], 400);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return new JsonResponse([
                'msg' => new DangerMessage('Error during creation operation.'),
                'success' => false,
                'data' => [
                    'validation' => [],
                    'user' => null,
                ]
            ], 400);
        }
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
