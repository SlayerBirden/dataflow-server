<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Pipeline\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Pipeline\Validation\PipeValidator;
use Zend\ServiceManager\Factory\FactoryInterface;

final class PipeValidatorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PipeValidator($container->get('PipeRepository'));
    }
}
