<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\DecoratedStrategy;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\Decoration\NullDecorator;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\NestedEntityStrategy;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\NamingStrategy\MapNamingStrategy;
use Zend\Hydrator\Strategy\DateTimeFormatterStrategy;
use Zend\ServiceManager\Factory\FactoryInterface;

final class HistoryHydratorFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $permissionStrat = new NestedEntityStrategy((new PermissionHydratorFactory())($container, $requestedName));

        $hydrator = new ClassMethods();
        $hydrator->addStrategy('user', new NestedEntityStrategy(new ClassMethods()));
        $hydrator->addStrategy('owner', new NestedEntityStrategy(new ClassMethods()));
        $hydrator->addStrategy(
            'permission',
            new DecoratedStrategy($permissionStrat, new NullDecorator())
        );
        $hydrator->addStrategy('at', new DateTimeFormatterStrategy());

        $hydrator->setNamingStrategy(new MapNamingStrategy([
            'change_action' => 'changeAction',
        ], [
            'changeAction' => 'change_action',
        ]));

        return $hydrator;
    }
}
