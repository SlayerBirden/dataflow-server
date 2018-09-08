<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator;

use Zend\Hydrator\HydratorInterface;

final class ListExtractor
{
    public function __invoke(HydratorInterface $hydrator, array $objects): array
    {
        return array_map([$hydrator, 'extract'], $objects);
    }
}
