<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Stdlib;

use Psr\Http\Message\ResponseInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;

final class ResponseFactory
{
    public function __invoke(
        string $message,
        $code,
        ?string $dataObjectName = null,
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
            'msg' => $this->getMessage($message, $code),
        ], $code);
    }

    private function getMessage($message, $code)
    {
        if ($code >= 200 and $code < 300) {
            return new SuccessMessage($message);
        } else {
            return new DangerMessage($message);
        }
    }
}
