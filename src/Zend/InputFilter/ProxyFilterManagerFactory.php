<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Zend\InputFilter;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ProxyFilterManagerFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $filters = $container->get('InputFilterManager');

        if ($filters) {
            return $filters->get($requestedName);
        }

        throw new ServiceNotFoundException(sprintf('Could not identify input filter by the name %s.', $requestedName));
    }
}
