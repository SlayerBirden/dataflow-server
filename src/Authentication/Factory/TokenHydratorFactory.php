<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Grant;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\CollectionStrategy;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\NestedEntityStrategy;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\NamingStrategy\MapNamingStrategy;
use Zend\Hydrator\Strategy\BooleanStrategy;
use Zend\Hydrator\Strategy\DateTimeFormatterStrategy;
use Zend\ServiceManager\Factory\FactoryInterface;

final class TokenHydratorFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $hydrator = new ClassMethods();
        $hydrator->addStrategy('created_at', new DateTimeFormatterStrategy());
        $hydrator->addStrategy('due', new DateTimeFormatterStrategy());
        $hydrator->addStrategy('active', new BooleanStrategy(1, 0));
        $hydrator->addStrategy('grants', new CollectionStrategy(new ClassMethods(), Grant::class));
        $hydrator->addStrategy('owner', new NestedEntityStrategy(new ClassMethods()));

        $hydrator->setNamingStrategy(new MapNamingStrategy([], [
            'isActive' => 'active',
        ]));

        return $hydrator;
    }
}
