<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Factory;

use Psr\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\NestedEntityStrategy;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\ExtractionInterface;

class DbConfigExtractionFactory
{
    public function __invoke(ContainerInterface $container): ExtractionInterface
    {
        $extraction = new ClassMethods();
        $extraction->addStrategy('owner', new NestedEntityStrategy(new ClassMethods()));

        return $extraction;
    }
}
