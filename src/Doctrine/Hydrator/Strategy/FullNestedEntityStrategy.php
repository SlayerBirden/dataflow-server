<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy;

use Doctrine\ORM\EntityNotFoundException;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use Zend\Hydrator\ExtractionInterface;
use Zend\Hydrator\Strategy\StrategyInterface;

final class FullNestedEntityStrategy implements StrategyInterface
{
    /**
     * @var ExtractionInterface
     */
    private $extraction;
    /**
     * @var EntityManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var string
     */
    private $entityClassName;

    public function __construct(
        ExtractionInterface $extraction,
        EntityManagerRegistry $managerRegistry,
        string $entityClassName
    ) {
        $this->extraction = $extraction;
        $this->managerRegistry = $managerRegistry;
        $this->entityClassName = $entityClassName;
    }

    /**
     * Assumption is - we're getting an object here
     *
     * @inheritdoc
     */
    public function extract($value)
    {
        return $this->extraction->extract($value);
    }

    /**
     * Assumption is - we're getting id here
     *
     * @inheritdoc
     * @throws \Doctrine\ORM\ORMException
     */
    public function hydrate($value)
    {
        $em = $this->managerRegistry->getManagerForClass($this->entityClassName);
        $entity = $em->find($this->entityClassName, $value);

        if ($entity === null) {
            throw new EntityNotFoundException(sprintf('Pipe Type %s does not exist', $value));
        }

        return $entity;
    }
}
