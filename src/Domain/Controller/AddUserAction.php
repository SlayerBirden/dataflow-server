<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

final class AddUserAction implements MiddlewareInterface
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
            return (new DataValidationResponseFactory())('user');
        }
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('user', $this->inputFilter);
        }
        try {
            $entity = $this->getEntity($data);
            $em = $this->managerRegistry->getManagerForClass(User::class);
            $em->persist($entity);
            $em->flush();
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
