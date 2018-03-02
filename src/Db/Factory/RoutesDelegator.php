<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Factory;

use Psr\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Db\Controller\AddConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\DeleteConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\GetConfigAction;
use SlayerBirden\DataFlowServer\Db\Controller\GetConfigsAction;
use SlayerBirden\DataFlowServer\Db\Controller\UpdateConfigAction;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;

class RoutesDelegator
{
    /**
     * @param ContainerInterface $container
     * @param string $serviceName
     * @param callable $callback
     * @return Application
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback)
    {
        /** @var $app Application */
        $app = $callback();

        $app->get('/config/{id:\d+}', [
            GetConfigAction::class
        ], 'get_config');

        $app->get('/configs', [
            GetConfigsAction::class
        ], 'get_configs');

        $app->post('/config', [
            BodyParamsMiddleware::class,
            AddConfigAction::class
        ], 'add_config');

        $app->put('/config/{id:\d+}', [
            BodyParamsMiddleware::class,
            UpdateConfigAction::class
        ], 'update_config');

        $app->delete('/config/{id:\d+}', [
            DeleteConfigAction::class
        ], 'delete_config');

        return $app;
    }
}
