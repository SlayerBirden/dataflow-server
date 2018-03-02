<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Zend\InputFilter;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ImprovedInputFilterPluginManagerFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ImprovedInputFilterPluginManager($container);
    }
}
