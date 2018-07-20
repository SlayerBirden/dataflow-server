<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Doctrine\Middleware\BaseResourceMiddleware;
use Zend\ServiceManager\Factory\FactoryInterface;

class TokenResourceMiddlewareFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new BaseResourceMiddleware(
            $container->get(EntityManager::class),
            $container->get(LoggerInterface::class),
            Token::class,
            'token'
        );
    }
}
