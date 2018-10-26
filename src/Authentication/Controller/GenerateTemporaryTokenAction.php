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
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
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
        $data = Parser::getRequestBody($request);
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
            return (new ResponseFactory())('Token created', 200, 'token', $this->hydrator->extract($token));
        } catch (PermissionDeniedException $exception) {
            return (new ResponseFactory())($exception->getMessage(), 400, 'token');
        }
    }
}
