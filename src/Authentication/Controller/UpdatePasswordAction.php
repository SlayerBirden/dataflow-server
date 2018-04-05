<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;

class UpdatePasswordAction implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute(TokenMiddleware::USER_PARAM);

        if (empty($user)) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('No active user detected.'),
            ], 403);
        }


    }
}
