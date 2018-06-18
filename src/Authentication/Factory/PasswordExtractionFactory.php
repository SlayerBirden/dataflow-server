<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Factory;

use Psr\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\NestedEntityStrategy;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\NamingStrategy\MapNamingStrategy;
use Zend\Hydrator\Strategy\BooleanStrategy;
use Zend\Hydrator\Strategy\DateTimeFormatterStrategy;

class PasswordExtractionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $extraction = new ClassMethods();
        $extraction->addStrategy('created_at', new DateTimeFormatterStrategy());
        $extraction->addStrategy('due', new DateTimeFormatterStrategy());
        $extraction->addStrategy('active', new BooleanStrategy(1, 0));
        $extraction->addStrategy('owner', new NestedEntityStrategy(new ClassMethods()));

        $extraction->setNamingStrategy(new MapNamingStrategy([
            'created_at' => 'createdAt'
        ], [
            'isActive' => 'active',
            'createdAt' => 'created_at',
        ]));

        return $extraction;
    }
}
