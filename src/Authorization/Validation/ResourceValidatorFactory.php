<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Validation;

use Psr\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Authorization\ResourceManagerInterface;

class ResourceValidatorFactory
{
    /**
     * @param ContainerInterface $container
     * @return ResourceValidator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ResourceValidator
    {
        return new ResourceValidator(null, $container->get(ResourceManagerInterface::class));
    }
}
