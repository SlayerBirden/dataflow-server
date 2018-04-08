<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy;

use Doctrine\Common\Collections\Collection;
use ReflectionClass;
use Zend\Hydrator\Exception\InvalidArgumentException;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\Strategy\StrategyInterface;

class CollectionStrategy implements StrategyInterface
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
    public function __construct(HydratorInterface $objectHydrator, $objectClassName)
    {
        if (! is_string($objectClassName) || ! class_exists($objectClassName)) {
            throw new InvalidArgumentException(sprintf(
                'Object class name needs to the name of an existing class, got "%s" instead.',
                is_object($objectClassName) ? get_class($objectClassName) : gettype($objectClassName)
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
    public function hydrate($value)
    {
        if (! ($value instanceof Collection)) {
            throw new InvalidArgumentException(sprintf(
                'Value needs to be a Doctrine Collection, got %s instead.',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        $reflection = new ReflectionClass($this->objectClassName);

        return array_map(function ($data) use ($reflection) {
            return $this->objectHydrator->hydrate(
                $data,
                $reflection->newInstanceWithoutConstructor()
            );
        }, $value);
    }
}
