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
        $extraction = new ClassMethods();
        $extraction->addStrategy('created_at', new DateTimeFormatterStrategy());
        $extraction->addStrategy('due', new DateTimeFormatterStrategy());
        $extraction->addStrategy('active', new BooleanStrategy(1, 0));
        $extraction->addStrategy('grants', new CollectionStrategy(new ClassMethods(), Grant::class));
        $extraction->addStrategy('owner', new NestedEntityStrategy(new ClassMethods()));

        $extraction->setNamingStrategy(new MapNamingStrategy([], [
            'isActive' => 'active',
        ]));

        return $extraction;
    }
}
