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
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;
use SlayerBirden\DataFlowServer\Stdlib\ResponseFactory;
use SlayerBirden\DataFlowServer\Validation\Exception\ValidationException;
use Zend\Hydrator\HydratorInterface;

final class UpdateUserAction implements MiddlewareInterface
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;
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
        LoggerInterface $logger
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->hydrator = $hydrator;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = Parser::getRequestBody($request);
        $user = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);
        try {
            $this->hydrator->hydrate($data, $user);
            $em = $this->managerRegistry->getManagerForClass(User::class);
            $em->persist($user);
            $em->flush();
            return (new ResponseFactory())('User has been updated!', 200, 'user', $this->hydrator->extract($user));
        } catch (ORMInvalidArgumentException | ValidationException $exception) {
            return (new ResponseFactory())($exception->getMessage(), 400, 'user');
        } catch (UniqueConstraintViolationException $exception) {
            $msg = 'Email address already taken.';
            $userData = isset($user) ? $this->hydrator->extract($user) : null;
            return (new ResponseFactory())($msg, 400, 'user', $userData);
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return (new ResponseFactory())('Error saving user.', 400, 'user');
        }
    }
}
