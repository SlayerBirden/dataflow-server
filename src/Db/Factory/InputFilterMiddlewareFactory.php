<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Zend\InputFilter\Middleware\InputFilterMiddleware;
use Zend\ServiceManager\Factory\FactoryInterface;

final class InputFilterMiddlewareFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): InputFilterMiddleware {
        return new InputFilterMiddleware($container->get('ConfigInputFilter'), 'configuration');
    }
}
