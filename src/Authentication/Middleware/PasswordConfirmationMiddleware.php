<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;

class PasswordConfirmationMiddleware implements MiddlewareInterface
{
    /**
     * @var PasswordManagerInterface
     */
    private $passwordManager;

    public function __construct(PasswordManagerInterface $passwordManager)
    {
        $this->passwordManager = $passwordManager;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();

        $password = $data['password'] ?? null;

        if (empty($password)) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('The action requires password confirmation. No password provided.'),
            ], 412);
        }

        $user = $request->getAttribute(TokenMiddleware::USER_PARAM);

        if (empty($user)) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('The action requires active user.'),
            ], 412);
        }

        if (!$this->passwordManager->isValid($password, $user)) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('Invalid password provided.'),
            ], 412);
        }

        return $handler->handle($request);
    }
}
