<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Middleware;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;

class TokenResourceMiddleware implements ResourceMiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id');

        if ($id !== null) {
            try {
                $token = $this->entityManager->find(Token::class, $id);
                if ($token) {
                    return $handler->handle(
                        $request->withAttribute(self::DATA_RESOURCE, $token)
                    );
                } else {
                    return new JsonResponse([
                        'data' => [
                            'token' => null,
                        ],
                        'success' => false,
                        'msg' => new DangerMessage('Could not load Token by provided ID.'),
                    ], 404);
                }
            } catch (ORMInvalidArgumentException | ORMException $exception) {
                $this->logger->error((string)$exception);
                return new JsonResponse([
                    'data' => [
                        'token' => null,
                    ],
                    'success' => false,
                    'msg' => new DangerMessage('Error during loading Token.'),
                ], 404);
            }
        } else {
            return new JsonResponse([
                'data' => [
                    'token' => null,
                ],
                'success' => false,
                'msg' => new DangerMessage('No id provided.'),
            ], 404);
        }
    }
}
