<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;

class ObscuredStrategy implements StrategyInterface
{
    /**
     * @inheritdoc
     */
    public function extract($value)
    {
        if ($value !== null) {
            return '*****';
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function hydrate($value)
    {
        return $value;
    }
}
