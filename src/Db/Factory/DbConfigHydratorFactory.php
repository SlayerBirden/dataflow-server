<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Factory;

use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\NestedEntityStrategy;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\ObscuredStrategy;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\RegexpObscuredStrategy;
use Zend\Hydrator\ClassMethods;
use Zend\ServiceManager\Factory\FactoryInterface;

class DbConfigHydratorFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        $extraction = new ClassMethods();
        $extraction->addStrategy('owner', new NestedEntityStrategy(new ClassMethods()));
        $extraction->addStrategy('password', new ObscuredStrategy());
        $extraction->addStrategy('url', new RegexpObscuredStrategy(
            '/:\w+@/',
            ':*****@'
        ));

        return $extraction;
    }
}