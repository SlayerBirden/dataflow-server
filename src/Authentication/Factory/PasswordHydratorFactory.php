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
        $extraction = new ClassMethods();
        $extraction->addStrategy('created_at', new DateTimeFormatterStrategy());
        $extraction->addStrategy('due', new DateTimeFormatterStrategy());
        $extraction->addStrategy('active', new BooleanStrategy(1, 0));
        $extraction->addStrategy('owner', new NestedEntityStrategy(new ClassMethods()));
        $extraction->addStrategy('hash', $container->get(HashStrategy::class));

        $extraction->setNamingStrategy(new MapNamingStrategy([
            'created_at' => 'createdAt',
            'password' => 'hash',
        ], [
            'isActive' => 'active',
            'createdAt' => 'created_at',
        ]));

        return $extraction;
    }
}
