<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\ExtractionNestedEntityStrategy;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\ObscuredStrategy;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\RegexpObscuredStrategy;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\HydratorInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

final class DbConfigHydratorFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): HydratorInterface {
        $extraction = new ClassMethods();
        $extraction->addStrategy('owner', new ExtractionNestedEntityStrategy(new ClassMethods()));
        $extraction->addStrategy('password', new ObscuredStrategy());
        $extraction->addStrategy('url', new RegexpObscuredStrategy(
            '/:\w+@/',
            ':*****@'
        ));

        return $extraction;
    }
}
