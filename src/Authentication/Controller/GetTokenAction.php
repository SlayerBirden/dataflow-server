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
use Zend\Hydrator\ExtractionInterface;

class GetTokenAction implements MiddlewareInterface
{
    /**
     * @var TokenManagerInterface
     */
    private $tokenManager;
    /**
     * @var ExtractionInterface
     */
    private $extraction;

    public function __construct(TokenManagerInterface $tokenManager, ExtractionInterface $extraction)
    {
        $this->tokenManager = $tokenManager;
        $this->extraction = $extraction;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $user = $data['user'] ?? null;
        $password = $data['password'] ?? null;
        $resources = $data['resources'] ?? [];

        $status = 401;
        $success = false;
        $token = null;
        $msg = null;

        if ($user === null) {
            return new JsonResponse([
                'data' => [
                    'token' => $token,
                ],
                'success' => $success,
                'msg' => new DangerMessage('Empty user.'),
            ], $status);
        }
        if ($password === null) {
            return new JsonResponse([
                'data' => [
                    'token' => $token,
                ],
                'success' => $success,
                'msg' => new DangerMessage('Empty password.'),
            ], $status);
        }
        if (empty($resources)) {
            return new JsonResponse([
                'data' => [
                    'token' => $token,
                ],
                'success' => $success,
                'msg' => new DangerMessage('Please specify resources you want to access using the token.'),
            ], $status);
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
                'token' => $this->extraction->extract($token),
            ],
            'success' => $success,
            'msg' => $msg,
        ], $status);
    }
}
