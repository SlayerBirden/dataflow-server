<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Exception\InvalidCredentialsException;
use SlayerBirden\DataFlowServer\Authentication\Exception\PermissionDeniedException;
use SlayerBirden\DataFlowServer\Authentication\TokenManagerInterface;
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;
use SlayerBirden\DataFlowServer\Stdlib\ResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Hydrator\ExtractionInterface;
use Zend\InputFilter\InputFilterInterface;

final class GetTokenAction implements MiddlewareInterface
{
    /**
     * @var TokenManagerInterface
     */
    private $tokenManager;
    /**
     * @var ExtractionInterface
     */
    private $extraction;
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TokenManagerInterface $tokenManager,
        ExtractionInterface $extraction,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger
    ) {
        $this->tokenManager = $tokenManager;
        $this->extraction = $extraction;
        $this->inputFilter = $inputFilter;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = Parser::getRequestBody($request);
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('token', $this->inputFilter);
        }

        try {
            $token = $this->tokenManager->getToken($data['user'], $data['password'], $data['resources']);
            $msg = 'Token successfully created';
            return (new ResponseFactory())($msg, 200, 'token', $this->extraction->extract($token));
        } catch (InvalidCredentialsException $exception) {
            $msg = 'Invalid credentials provided. Please double check your user and password.';
            return (new ResponseFactory())($msg, 401, 'token');
        } catch (PermissionDeniedException $exception) {
            $msg = 'Provided user does not have permission to access requested resources.';
            return (new ResponseFactory())($msg, 403, 'token');
        }
    }
}
