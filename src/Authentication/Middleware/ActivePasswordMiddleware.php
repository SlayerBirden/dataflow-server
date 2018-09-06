<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Middleware;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;

final class ActivePasswordMiddleware implements MiddlewareInterface
{
    /**
     * @var Selectable
     */
    private $passwordRepository;

    public function __construct(Selectable $passwordRepository)
    {
        $this->passwordRepository = $passwordRepository;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute(TokenMiddleware::USER_PARAM);

        if ($this->hasActivePassword($user)) {
            $msg = 'You already have an active password. Please use "update" routine.';
            return (new GeneralErrorResponseFactory())($msg, 'password', 412);
        }

        return $handler->handle($request);
    }

    private function hasActivePassword(User $user): bool
    {
        $collection = $this->passwordRepository->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('owner', $user))
                ->andWhere(Criteria::expr()->eq('active', true))
        );

        return !$collection->isEmpty();
    }
}
