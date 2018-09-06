<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;

final class RegexpObscuredStrategy implements StrategyInterface
{
    /**
     * @var string
     */
    private $pattern;
    /**
     * @var string
     */
    private $replacement;

    public function __construct(string $pattern, string $replacement = '')
    {
        $this->pattern = $pattern;
        $this->replacement = $replacement;
    }

    /**
     * @inheritdoc
     */
    public function extract($value)
    {
        if (!empty($value) && preg_match($this->pattern, $value)) {
            return preg_replace($this->pattern, $this->replacement, $value);
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
