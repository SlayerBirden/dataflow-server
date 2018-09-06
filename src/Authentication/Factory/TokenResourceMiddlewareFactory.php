<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Factory;

use Doctrine\Common\Persistence\ManagerRegistry;
use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\BaseResourceMiddleware;
use Zend\ServiceManager\Factory\FactoryInterface;

final class TokenResourceMiddlewareFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new BaseResourceMiddleware(
            $container->get(ManagerRegistry::class),
            $container->get(LoggerInterface::class),
            Token::class,
            'token'
        );
    }
}
