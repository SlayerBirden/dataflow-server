<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Exception\PermissionDeniedException;
use SlayerBirden\DataFlowServer\Authentication\TokenManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;
use Zend\InputFilter\InputFilterInterface;

class GenerateTemporaryTokenAction implements MiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var TokenManagerInterface
     */
    private $tokenManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ExtractionInterface
     */
    private $extraction;
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;

    public function __construct(
        EntityManager $entityManager,
        InputFilterInterface $inputFilter,
        TokenManagerInterface $tokenManager,
        LoggerInterface $logger,
        ExtractionInterface $extraction
    ) {
        $this->entityManager = $entityManager;
        $this->tokenManager = $tokenManager;
        $this->logger = $logger;
        $this->extraction = $extraction;
        $this->inputFilter = $inputFilter;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userId = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $this->inputFilter->setData($data);

        if ($this->inputFilter->isValid()) {
            return $this->createToken($userId, $data['resources']);
        } else {
            return (new ValidationResponseFactory())('token', $this->inputFilter);
        }
    }

    private function createToken(int $userId, array $resources): ResponseInterface
    {
        try {
            /** @var User $user */
            $user = $this->entityManager->find(User::class, $userId);
            if ($user) {
                $token = $this->tokenManager->getTmpToken($user, $resources);
                return new JsonResponse([
                    'data' => [
                        'token' => $this->extraction->extract($token),
                        'validation' => [],
                    ],
                    'success' => true,
                    'msg' => new SuccessMessage('Token created'),
                ], 200);
            } else {
                $msg = new DangerMessage('User not found');
            }
        } catch (PermissionDeniedException $exception) {
            $msg = new DangerMessage($exception->getMessage());
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $msg = new DangerMessage('There was an error while obtaining tmp token.');
        }

        return new JsonResponse([
            'data' => [
                'token' => null,
                'validation' => [],
            ],
            'success' => false,
            'msg' => $msg,
        ], 400);
    }
}
