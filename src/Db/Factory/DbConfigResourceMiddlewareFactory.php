<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Factory;

use Doctrine\Common\Persistence\ManagerRegistry;
use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\BaseResourceMiddleware;
use Zend\ServiceManager\Factory\FactoryInterface;

final class DbConfigResourceMiddlewareFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new BaseResourceMiddleware(
            $container->get(ManagerRegistry::class),
            $container->get(LoggerInterface::class),
            DbConfiguration::class,
            'configuration'
        );
    }
}
