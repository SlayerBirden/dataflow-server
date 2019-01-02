<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy;

use Zend\Hydrator\ExtractionInterface;
use Zend\Hydrator\Strategy\StrategyInterface;

final class ExtractionNestedEntityStrategy implements StrategyInterface
{
    /**
     * @var ExtractionInterface
     */
    private $extraction;

    public function __construct(ExtractionInterface $extraction)
    {
        $this->extraction = $extraction;
    }

    /**
     * @inheritdoc
     */
    public function extract($value)
    {
        return $this->extraction->extract($value);
    }

    /**
     * @inheritdoc
     */
    public function hydrate($value)
    {
        return $value;
    }
}
