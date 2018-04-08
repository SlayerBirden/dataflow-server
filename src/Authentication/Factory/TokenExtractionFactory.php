<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Factory;

use Psr\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Grant;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\CollectionStrategy;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\NestedEntityStrategy;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\Strategy\BooleanStrategy;
use Zend\Hydrator\Strategy\DateTimeFormatterStrategy;

class TokenExtractionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $extraction = new ClassMethods();
        $extraction->addStrategy('created_at', new DateTimeFormatterStrategy());
        $extraction->addStrategy('due', new DateTimeFormatterStrategy());
        $extraction->addStrategy('active', new BooleanStrategy(1, 0));
        $extraction->addStrategy('grants', new CollectionStrategy(new ClassMethods(), Grant::class));
        $extraction->addStrategy('owner', new NestedEntityStrategy(new ClassMethods()));

        return $extraction;
    }
}
