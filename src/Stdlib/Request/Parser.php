<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Stdlib\Request;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Hydrator\HydratorInterface;

final class Parser
{
    public static function getRequestBody(ServerRequestInterface $request): array
    {
        $data = $request->getParsedBody();
        if ($data === null) {
            $data = [];
        }
        if (is_object($data)) {
            $data = self::extract($data);
        }

        return $data;
    }

    private static function extract($object): array
    {
        if (is_iterable($object)) {
            $result = [];
            foreach ($object as $key => $value) {
                if (is_object($value)) {
                    $result[$key] = self::extract($object);
                } else {
                    $result[$key] = $value;
                }
            }
            return $result;
        } else {
            $config = new \GeneratedHydrator\Configuration(get_class($object));
            $hydratorClass = $config->createFactory()->getHydratorClass();
            /** @var HydratorInterface $hydrator */
            $hydrator = new $hydratorClass();
            return $hydrator->extract($object);
        }
    }
}
