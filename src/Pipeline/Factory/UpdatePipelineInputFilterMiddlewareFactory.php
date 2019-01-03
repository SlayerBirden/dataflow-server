<?php

/**
 * This file is generated by SlayerBirden\DFCodeGeneration
 */

declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Pipeline\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Zend\InputFilter\Middleware\InputFilterMiddleware;
use Zend\ServiceManager\Factory\FactoryInterface;

final class UpdatePipelineInputFilterMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InputFilterMiddleware
    {
        return new InputFilterMiddleware($container->get('UpdatePipelineInputFilter'), 'pipeline');
    }
}
