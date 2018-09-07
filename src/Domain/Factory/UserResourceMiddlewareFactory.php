<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Factory;

use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\BaseResourceMiddleware;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use Zend\ServiceManager\Factory\FactoryInterface;

final class UserResourceMiddlewareFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new BaseResourceMiddleware(
            $container->get(EntityManagerRegistry::class),
            $container->get(LoggerInterface::class),
            User::class,
            'user'
        );
    }
}
