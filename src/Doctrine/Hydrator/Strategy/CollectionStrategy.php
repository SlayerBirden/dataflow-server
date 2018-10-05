<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Zend\Hydrator\Exception\InvalidArgumentException;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\Strategy\StrategyInterface;

final class CollectionStrategy implements StrategyInterface
{
    /**
     * @var HydratorInterface
     */
    private $objectHydrator;
    /**
     * @var string
     */
    private $objectClassName;

    /**
     * @param HydratorInterface $objectHydrator
     * @param string $objectClassName
     *
     * @throws InvalidArgumentException
     */
    public function __construct(HydratorInterface $objectHydrator, string $objectClassName)
    {
        if (! class_exists($objectClassName)) {
            throw new InvalidArgumentException(sprintf(
                'Object class name does not exist: "%s".',
                $objectClassName
            ));
        }

        $this->objectHydrator = $objectHydrator;
        $this->objectClassName = $objectClassName;
    }

    /**
     * @inheritdoc
     */
    public function extract($value)
    {
        if (! ($value instanceof Collection)) {
            throw new InvalidArgumentException(sprintf(
                'Value needs to be a Doctrine Collection, got %s instead.',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        return array_map(function ($object) {
            if (! $object instanceof $this->objectClassName) {
                throw new InvalidArgumentException(sprintf(
                    'Value needs to be an instance of "%s", got "%s" instead.',
                    $this->objectClassName,
                    is_object($object) ? get_class($object) : gettype($object)
                ));
            }

            return $this->objectHydrator->extract($object);
        }, $value->toArray());
    }

    /**
     * @inheritdoc
     */
    public function hydrate($value): Collection
    {
        if (! is_iterable($value)) {
            throw new InvalidArgumentException(sprintf(
                'Value needs to be an Iterable, got %s instead.',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        $collection = new ArrayCollection();
        foreach ($value as $item) {
            $collection->add($this->objectHydrator->hydrate(
                $item,
                new $this->objectClassName
            ));
        }

        return $collection;
    }
}
