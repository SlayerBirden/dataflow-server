<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;

final class ObscuredStrategy implements StrategyInterface
{
    const OBSCURED_STRING = '*****';

    /**
     * @inheritdoc
     */
    public function extract($value)
    {
        if ($value !== null) {
            return self::OBSCURED_STRING;
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
