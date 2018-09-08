<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\Decoration;

use Zend\Hydrator\Strategy\StrategyInterface;

final class NullDecorator implements DecoratorInterface
{
    public function getExtracted(StrategyInterface $strategy, $value)
    {
        if ($value === null) {
            return null;
        }

        return $strategy->extract($value);
    }
}
