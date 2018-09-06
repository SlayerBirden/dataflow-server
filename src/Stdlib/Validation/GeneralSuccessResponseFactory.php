<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Stdlib\Validation;

use Psr\Http\Message\ResponseInterface;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;

final class GeneralSuccessResponseFactory
{
    public function __invoke(
        string $message,
        string $dataObjectName,
        $value,
        $code = 200,
        $count = null
    ): ResponseInterface {
        $data = [
            $dataObjectName => $value,
            'validation' => [],
        ];
        if ($count !== null) {
            $data['count'] = $count;
        }
        return new JsonResponse([
            'data' => $data,
            'success' => true,
            'msg' => new SuccessMessage($message),
        ], $code);
    }
}
