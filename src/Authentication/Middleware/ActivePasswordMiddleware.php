<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Middleware;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;

class ActivePasswordMiddleware implements MiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute(TokenMiddleware::USER_PARAM);

        if ($this->hasActivePassword($user)) {
            return new JsonResponse([
                'data' => [
                    'validation' => [],
                    'password' => null,
                ],
                'success' => false,
                'msg' => new DangerMessage('You already have an active password. Please use "update" routine.'),
            ], 412);
        }

        return $handler->handle($request);
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
