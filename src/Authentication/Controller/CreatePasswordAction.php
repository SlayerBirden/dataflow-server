<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

class CreatePasswordAction implements MiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(
        EntityManager $entityManager,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger,
        HydratorInterface $hydrator
    ) {
        $this->entityManager = $entityManager;
        $this->inputFilter = $inputFilter;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $this->inputFilter->setData($data);

        if ($this->inputFilter->isValid()) {
            try {
                return $this->createPassword($data);
            } catch (\Exception $exception) {
                return new JsonResponse([
                    'msg' => new DangerMessage('There was an error while creating password.'),
                    'success' => true,
                    'data' => [
                        'validation' => [],
                        'password' => null,
                    ]
                ], 500);
            }
        } else {
            return (new ValidationResponseFactory())('password', $this->inputFilter);
        }
    }

    /**
     * @param array $data
     * @return ResponseInterface
     * @throws \Exception
     */
    private function createPassword(array $data): ResponseInterface
    {
        try {
            $data['created_at'] = (new \DateTime())->format(\DateTime::RFC3339);
            $data['due'] = (new \DateTime())->add(new \DateInterval('P1Y'))->format(\DateTime::RFC3339);
            $data['active'] = $data['active'] ?? true;
            $password = $this->hydrator->hydrate($data, new Password());
            $this->entityManager->persist($password);
            $this->entityManager->flush();
            return new JsonResponse([
                'msg' => new SuccessMessage('Password has been successfully created!'),
                'success' => true,
                'data' => [
                    'validation' => [],
                    'password' => $this->hydrator->extract($password),
                ]
            ], 200);
        } catch (ORMInvalidArgumentException | \InvalidArgumentException $exception) {
            return new JsonResponse([
                'msg' => new DangerMessage($exception->getMessage()),
                'success' => false,
                'data' => [
                    'validation' => [],
                    'password' => null,
                ]
            ], 400);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return new JsonResponse([
                'msg' => new DangerMessage('There was an error creating password. Please check your request.'),
                'success' => false,
                'data' => [
                    'validation' => [],
                    'password' => null,
                ]
            ], 400);
        }
    }
}
