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
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;
use SlayerBirden\DataFlowServer\Stdlib\ResponseFactory;
use SlayerBirden\DataFlowServer\Validation\Exception\ValidationException;
use Zend\Hydrator\HydratorInterface;

final class AddUserAction implements MiddlewareInterface
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
        try {
            $entity = new User();
            $this->hydrator->hydrate($data, $entity);
            $em = $this->managerRegistry->getManagerForClass(User::class);
            $em->persist($entity);
            $em->flush();
            $msg = 'User has been successfully created!';
            return (new ResponseFactory())($msg, 200, 'user', $this->hydrator->extract($entity));
        } catch (ORMInvalidArgumentException | ValidationException $exception) {
            return (new ResponseFactory())($exception->getMessage(), 400, 'user');
        } catch (UniqueConstraintViolationException $exception) {
            return (new ResponseFactory())('Provided email already exists.', 400, 'user');
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return (new ResponseFactory())('Error during creation operation.', 400, 'user');
        }
    }
}
