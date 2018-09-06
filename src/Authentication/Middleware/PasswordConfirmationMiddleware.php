<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;

final class PasswordConfirmationMiddleware implements MiddlewareInterface
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
        if (!is_array($data)) {
            return (new DataValidationResponseFactory())();
        }
        $password = $data['password'] ?? null;

        if (empty($password)) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('The action requires password confirmation. No password provided.'),
            ], 412);
        } else {
            unset($data['password']);
        }

        $user = $request->getAttribute(TokenMiddleware::USER_PARAM);
        if (!$this->passwordManager->isValidForUser((string)$password, $user)) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('Invalid password provided.'),
            ], 412);
        }

        // serve down the pipe without password data
        return $handler->handle($request->withParsedBody($data));
    }
}
