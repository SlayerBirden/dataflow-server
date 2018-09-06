<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Exception\PermissionDeniedException;
use SlayerBirden\DataFlowServer\Authentication\TokenManagerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

final class GenerateTemporaryTokenAction implements MiddlewareInterface
{
    /**
     * @var TokenManagerInterface
     */
    private $tokenManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;

    public function __construct(
        InputFilterInterface $inputFilter,
        TokenManagerInterface $tokenManager,
        LoggerInterface $logger,
        HydratorInterface $hydrator
    ) {
        $this->tokenManager = $tokenManager;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
        $this->inputFilter = $inputFilter;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return (new DataValidationResponseFactory())('token');
        }
        $this->inputFilter->setData($data);

        $user = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);

        if ($this->inputFilter->isValid()) {
            return $this->createToken($user, $data['resources']);
        } else {
            return (new ValidationResponseFactory())('token', $this->inputFilter);
        }
    }

    private function createToken(User $user, array $resources): ResponseInterface
    {
        try {
            $token = $this->tokenManager->getTmpToken($user, $resources);
            return new JsonResponse([
                'data' => [
                    'token' => $this->hydrator->extract($token),
                    'validation' => [],
                ],
                'success' => true,
                'msg' => new SuccessMessage('Token created'),
            ], 200);
        } catch (PermissionDeniedException $exception) {
            return new JsonResponse([
                'data' => [
                    'token' => null,
                    'validation' => [],
                ],
                'success' => false,
                'msg' => new DangerMessage($exception->getMessage()),
            ], 400);
        } catch (\Exception $exception) {
            $this->logger->error((string)$exception);
            return new JsonResponse([
                'data' => [
                    'token' => null,
                    'validation' => [],
                ],
                'success' => false,
                'msg' => new DangerMessage('There was an error while obtaining tmp token.'),
            ], 500);
        }
    }
}
