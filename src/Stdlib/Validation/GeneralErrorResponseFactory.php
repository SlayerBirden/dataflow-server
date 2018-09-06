<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Stdlib\Validation;

use Psr\Http\Message\ResponseInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;

final class GeneralErrorResponseFactory
{
    public function __invoke(
        string $message,
        ?string $dataObjectName = null,
        $code = 500,
        $value = null,
        $count = null
    ): ResponseInterface {
        $data = [];
        if ($dataObjectName !== null) {
            $data[$dataObjectName] = $value;
            $data['validation'] = [];
        }
        if ($count !== null) {
            $data['count'] = $count;
        }
        return new JsonResponse([
            'data' => $data,
            'success' => false,
            'msg' => new DangerMessage($message),
        ], $code);
    }
}
