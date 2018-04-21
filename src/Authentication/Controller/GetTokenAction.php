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
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;
use Zend\InputFilter\InputFilterInterface;

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
        $this->inputFilter->setData($data);

        if ($this->inputFilter->isValid()) {
            try {
                $token = $this->tokenManager->getToken($data['user'], $data['password'], $data['resources']);
                return new JsonResponse([
                    'data' => [
                        'token' => $this->extraction->extract($token),
                        'validation' => [],
                    ],
                    'success' => true,
                    'msg' => new SuccessMessage('Token successfully creaeted'),
                ], 200);
            } catch (InvalidCredentialsException $exception) {
                $status = 401;
                $msg = new DangerMessage(
                    'Invalid credentials provided. Please double check your user and password.'
                );
            } catch (PermissionDeniedException $exception) {
                $status = 403;
                $msg = new DangerMessage('Provided user does not have permission to access requested resources.');
            }
        } else {
            return (new ValidationResponseFactory())('token', $this->inputFilter);
        }

        return new JsonResponse([
            'data' => [
                'token' => null,
            ],
            'success' => false,
            'msg' => $msg,
        ], $status);
    }
}
