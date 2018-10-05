<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Authentication\Hydrator\Strategy\HashStrategy;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\NestedEntityStrategy;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\NamingStrategy\MapNamingStrategy;
use Zend\Hydrator\Strategy\BooleanStrategy;
use Zend\Hydrator\Strategy\DateTimeFormatterStrategy;
use Zend\ServiceManager\Factory\FactoryInterface;

final class PasswordHydratorFactory implements FactoryInterface
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
        $hydrator->addStrategy('owner', new NestedEntityStrategy(new ClassMethods()));
        $hydrator->addStrategy('hash', $container->get(HashStrategy::class));

        $hydrator->setNamingStrategy(new MapNamingStrategy([
            'created_at' => 'createdAt',
            'password' => 'hash',
        ], [
            'isActive' => 'active',
            'createdAt' => 'created_at',
        ]));

        return $hydrator;
    }
}
