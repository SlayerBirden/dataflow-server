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
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
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

    public function __construct(
        TokenManagerInterface $tokenManager,
        ExtractionInterface $extraction,
        InputFilterInterface $inputFilter
    ) {
        $this->tokenManager = $tokenManager;
        $this->extraction = $extraction;
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

        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('token', $this->inputFilter);
        }

        try {
            $token = $this->tokenManager->getToken($data['user'], $data['password'], $data['resources']);
            $msg = 'Token successfully created';
            return (new GeneralSuccessResponseFactory())($msg, 'token', $this->extraction->extract($token));
        } catch (InvalidCredentialsException $exception) {
            $msg = 'Invalid credentials provided. Please double check your user and password.';
            return (new GeneralErrorResponseFactory())($msg, 'token', 401);
        } catch (PermissionDeniedException $exception) {
            $msg = 'Provided user does not have permission to access requested resources.';
            return (new GeneralErrorResponseFactory())($msg, 'token', 403);
        }
    }
}
