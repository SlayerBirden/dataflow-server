<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy;

use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\Decoration\DecoratorInterface;
use Zend\Hydrator\Strategy\StrategyInterface;

final class DecoratedStrategy implements StrategyInterface
{
    /**
     * @var StrategyInterface
     */
    private $strategy;
    /**
     * @var DecoratorInterface[]
     */
    private $decorators;

    public function __construct(StrategyInterface $strategy, DecoratorInterface ...$decorators)
    {
        $this->strategy = $strategy;
        $this->decorators = $decorators;
    }

    /**
     * {@inheritdoc}
     *
     * Decorate extraction
     */
    public function extract($value)
    {
        foreach ($this->decorators as $decorator) {
            $value = $decorator->getExtracted($this->strategy, $value);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function hydrate($value)
    {
        return $this->strategy->hydrate($value);
    }
}
