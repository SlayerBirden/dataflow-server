<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use SlayerBirden\DataFlowServer\Doctrine\Exception\NonExistingEntity;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

class UpdateUserAction implements MiddlewareInterface
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
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $id = (int)$request->getAttribute('id');

        $this->inputFilter->setData($data);

        $message = null;
        $validation = [];
        $updated = false;
        $status = 200;

        if ($this->inputFilter->isValid()) {
            try {
                $user = $this->getUser($id, $data);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $message = new SuccessMessage('User has been updated!');
                $updated = true;
            } catch (NonExistingEntity $exception) {
                $message = new DangerMessage($exception->getMessage());
                $status = 404;
            } catch (ORMInvalidArgumentException $exception) {
                $message = new DangerMessage($exception->getMessage());
                $status = 400;
            } catch (UniqueConstraintViolationException $exception) {
                $message = new DangerMessage('Email address already taken.');
                $status = 400;
            } catch (ORMException $exception) {
                $this->logger->error((string)$exception);
                $message = new DangerMessage('Error saving user.');
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
            'success' => $updated,
            'data' => [
                'validation' => $validation,
                'user' => isset($user) ? $this->extraction->extract($user) : null,
            ]
        ], $status);
    }

    private function getUser(int $id, array $data): User
    {
        /** @var User $user */
        $user = $this->entityManager->find(User::class, $id);
        if (!$user) {
            throw new NonExistingEntity(sprintf('Could not find user by id %d.', $id));
        }
        if (isset($data['id'])) {
            unset($data['id']);
        }
        $this->hydrator->hydrate($data, $user);

        return $user;
    }
}
