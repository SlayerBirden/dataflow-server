<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Stdlib\Validation;

use Psr\Http\Message\ResponseInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;

final class DataValidationResponseFactory
{
    /**
     * Create Data Validation response object
     *
     * @param string $dataObjectName
     * @param mixed $value
     * @return ResponseInterface
     */
    public function __invoke(
        ?string $dataObjectName = null,
        $value = null
    ): ResponseInterface {

        if (!empty($dataObjectName)) {
            $data = [
                $dataObjectName => $value,
            ];
        } else {
            $data = [];
        }

        return new JsonResponse([
            'data' => $data,
            'success' => false,
            'msg' => new DangerMessage('Provided data can not be read.'),
        ], 400);
    }
}
