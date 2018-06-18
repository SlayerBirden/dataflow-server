<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;
use Zend\Hydrator\HydrationInterface;
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
     * @var ExtractionInterface
     */
    private $extraction;
    /**
     * @var HydrationInterface
     */
    private $hydration;

    public function __construct(
        EntityManager $entityManager,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger,
        ExtractionInterface $extraction,
        HydrationInterface $hydration
    ) {
        $this->entityManager = $entityManager;
        $this->inputFilter = $inputFilter;
        $this->logger = $logger;
        $this->extraction = $extraction;
        $this->hydration = $hydration;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->validateUser($request);
        } catch (\DomainException $exception) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage($exception->getMessage()),
            ], $exception->getCode());
        }

        $data = $request->getParsedBody();
        $this->inputFilter->setData($data);

        if ($this->inputFilter->isValid()) {
            return $this->createPassword($data);
        } else {
            return (new ValidationResponseFactory())('password', $this->inputFilter);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @throws \DomainException
     */
    private function validateUser(ServerRequestInterface $request): void
    {
        $user = $request->getAttribute(TokenMiddleware::USER_PARAM);

        if ($this->hasActivePassword($user)) {
            throw new \DomainException(
                'You already have active password. Please use "update" routine.',
                412
            );
        }
    }

    private function createPassword(array $data): ResponseInterface
    {
        try {
            $password = $this->hydration->hydrate($data, new Password());
            $this->entityManager->persist($password);
            $this->entityManager->flush();
            return new JsonResponse([
                'msg' => new SuccessMessage('Password has been successfully created!'),
                'success' => true,
                'data' => [
                    'validation' => [],
                    'password' => $this->extraction->extract($password),
                ]
            ], 200);
        } catch (ORMInvalidArgumentException | \InvalidArgumentException $exception) {
            $message = new DangerMessage($exception->getMessage());
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $message = new DangerMessage('There was an error creating password.');
        }

        return new JsonResponse([
            'msg' => $message,
            'success' => false,
            'data' => [
                'validation' => [],
                'password' => null,
            ]
        ], 400);
    }

    private function hasActivePassword(User $user): bool
    {
        $collection = $this->entityManager
            ->getRepository(Password::class)
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->eq('owner', $user))
                    ->andWhere(Criteria::expr()->eq('active', true))
            );

        return !$collection->isEmpty();
    }
}
