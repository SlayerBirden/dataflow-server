<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Stdlib\Validation;

use Psr\Http\Message\ResponseInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\InputFilter\InputFilterInterface;

class ValidationResponseFactory
{
    /**
     * Create Validation response object
     *
     * @param string $dataObjectName
     * @param InputFilterInterface $inputFilter
     * @return ResponseInterface
     */
    public function __invoke(string $dataObjectName, InputFilterInterface $inputFilter): ResponseInterface
    {
        $validation = [];
        foreach ($inputFilter->getInvalidInput() as $key => $input) {
            $messages = $input->getMessages();
            $validation[] = [
                'field' => $key,
                'msg' => reset($messages)
            ];
        }

        return new JsonResponse([
            'data' => [
                $dataObjectName => null,
                'validation' => $validation,
            ],
            'success' => false,
            'msg' => new DangerMessage('There were validation errors.'),
        ], 400);
    }
}
