<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\Decoration;

use Zend\Hydrator\Strategy\StrategyInterface;

interface DecoratorInterface
{
    public function getExtracted(StrategyInterface $strategy, $value);
}
