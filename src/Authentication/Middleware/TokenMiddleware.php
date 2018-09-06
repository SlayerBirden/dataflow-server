<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Middleware;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router\RouteResult;

final class TokenMiddleware implements MiddlewareInterface
{
    const USER_PARAM = 'currentUser';
    /**
     * @var Selectable
     */
    private $tokenRepository;

    public function __construct(Selectable $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = $request->getHeader('Authorization');
        if (empty($authorization)) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('Empty Authorization header. Access denied.'),
            ], 401);
        }
        $token = $this->getToken((string)reset($authorization));
        if (!$token || !$token->isActive() || ($token->getDue() < new \DateTime())) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('Token is absent or invalid. Access denied.'),
            ], 401);
        }
        // check ACL
        $routeResult = $request->getAttribute(RouteResult::class, false);
        if (false === $routeResult) {
            // Can not perform ACL check
            return $handler->handle($request);
        }
        $routeName = $routeResult->getMatchedRouteName();
        foreach ($token->getGrants() as $grant) {
            if ($grant->getResource() === $routeName) {
                return $handler->handle($request->withAttribute(self::USER_PARAM, $token->getOwner()));
            }
        }

        return new JsonResponse([
            'data' => [],
            'success' => false,
            'msg' => new DangerMessage('The permission to resource is not granted.'),
        ], 403);
    }

    private function getToken(string $authorization): ?Token
    {
        $token = str_replace('Bearer ', '', $authorization);
        $tokens = $this->tokenRepository->matching(
            Criteria::create()->where(Criteria::expr()->eq('token', $token))
        );
        if ($tokens->count()) {
            return $tokens->first();
        }

        return null;
    }
}
