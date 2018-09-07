<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

final class UpdateUserAction implements MiddlewareInterface
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
     * @var EntityManagerRegistry
     */
    private $managerRegistry;

    public function __construct(
        EntityManagerRegistry $managerRegistry,
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
        $requestUser = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('user', $this->inputFilter);
        }
        try {
            $user = $this->getUser($requestUser, $data);
            $em = $this->managerRegistry->getManagerForClass(User::class);
            $em->persist($user);
            $em->flush();
            $msg = 'User has been updated!';
            return (new GeneralSuccessResponseFactory())($msg, 'user', $this->hydrator->extract($user));
        } catch (ORMInvalidArgumentException $exception) {
            return (new GeneralErrorResponseFactory())($exception->getMessage(), 'user', 400);
        } catch (UniqueConstraintViolationException $exception) {
            $msg = 'Email address already taken.';
            $userData = isset($user) ? $this->hydrator->extract($user) : null;
            return (new GeneralErrorResponseFactory())($msg, 'user', 400, $userData);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return (new GeneralErrorResponseFactory())('Error saving user.', 'user', 400);
        }
    }

    private function getUser(User $user, array $data): User
    {
        unset($data['id']);
        $this->hydrator->hydrate($data, $user);

        return $user;
    }
}
