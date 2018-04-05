<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Authentication\Controller\GenerateTemporaryTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

class RoutesDelegator implements DelegatorFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        /** @var $app Application */
        $app = $callback();

        $app->post(
            '/tmptoken/{id:\d+}',
            [
                TokenMiddleware::class,
                BodyParamsMiddleware::class,
                GenerateTemporaryTokenAction::class,
            ],
            'tmp_token'
        );

        return $app;
    }
}
