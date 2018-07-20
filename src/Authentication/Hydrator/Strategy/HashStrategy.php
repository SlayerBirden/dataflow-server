<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Hydrator\Strategy;

use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\ObscuredStrategy;
use Zend\Hydrator\Strategy\StrategyInterface;

class HashStrategy implements StrategyInterface
{
    /**
     * @var PasswordManagerInterface
     */
    private $passwordManager;

    public function __construct(PasswordManagerInterface $passwordManager)
    {
        $this->passwordManager = $passwordManager;
    }

    /**
     * @inheritdoc
     */
    public function extract($value)
    {
        return ObscuredStrategy::OBSCURED_STRING;
    }

    /**
     * @inheritdoc
     */
    public function hydrate($value)
    {
        return $this->passwordManager->getHash($value);
    }
}
