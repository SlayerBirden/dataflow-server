<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\ExtractionNestedEntityStrategy;
use Zend\Hydrator\ClassMethods;
use Zend\ServiceManager\Factory\FactoryInterface;

final class PermissionHydratorFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $hydrator = new ClassMethods();
        $hydrator->addStrategy('user', new ExtractionNestedEntityStrategy(new ClassMethods()));

        return $hydrator;
    }
}
