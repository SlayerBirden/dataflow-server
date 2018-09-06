<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Db\Controller\AddConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\DeleteConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\GetConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\GetConfigsAction;
use SlayerBirden\DataFlowServer\Db\Controller\UpdateConfigAction;
use SlayerBirden\DataFlowServer\Domain\Middleware\SetOwnerMiddleware;
use SlayerBirden\DataFlowServer\Domain\Middleware\ValidateOwnerMiddleware;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

final class RoutesDelegator implements DelegatorFactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        array $options = null
    ): Application {
        /** @var $app Application */
        $app = $callback();

        $app->get('/config/{id:\d+}', [
            TokenMiddleware::class,
            'DbConfigResourceMiddleware',
            GetConfigAction::class
        ], 'get_config');

        $app->get('/configs', [
            TokenMiddleware::class,
            GetConfigsAction::class
        ], 'get_configs');

        $app->post('/config', [
            TokenMiddleware::class,
            BodyParamsMiddleware::class,
            SetOwnerMiddleware::class,
            AddConfigAction::class
        ], 'add_config');

        $app->put('/config/{id:\d+}', [
            TokenMiddleware::class,
            'DbConfigResourceMiddleware',
            ValidateOwnerMiddleware::class,
            BodyParamsMiddleware::class,
            SetOwnerMiddleware::class,
            UpdateConfigAction::class
        ], 'update_config');

        $app->delete('/config/{id:\d+}', [
            TokenMiddleware::class,
            'DbConfigResourceMiddleware',
            ValidateOwnerMiddleware::class,
            DeleteConfigAction::class
        ], 'delete_config');

        return $app;
    }
}
