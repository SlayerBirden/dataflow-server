<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Authentication\Exception\InvalidCredentialsException;
use SlayerBirden\DataFlowServer\Authentication\Exception\PermissionDeniedException;
use SlayerBirden\DataFlowServer\Authentication\TokenManagerInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;

class GetTokenAction implements MiddlewareInterface
{
    /**
     * @var TokenManagerInterface
     */
    private $tokenManager;

    public function __construct(TokenManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $password = $request->getAttribute('password');
        $resources = $request->getAttribute('resources') ?? [];

        $status = 401;
        $success = false;
        $token = null;
        $msg = null;

        if ($user === null) {
            $msg = new DangerMessage('Empty user');
        }
        if ($password === null) {
            $msg = new DangerMessage('Empty password');
        }
        if ($user && $password) {
            try {
                $token = $this->tokenManager->getToken($user, $password, $resources);
                $status = 200;
                $success = true;
            } catch (InvalidCredentialsException $exception) {
                $msg = new DangerMessage('Invalid credentials provided. Please double check your user and password.');
            } catch (PermissionDeniedException $exception) {
                $status = 403;
                $msg = new DangerMessage('Provided user does not have permission to access requested resources.');
            }
        }

        return new JsonResponse([
            'data' => [
                'token' => $token,
            ],
            'success' => $success,
            'msg' => $msg,
        ], $status);
    }
}
