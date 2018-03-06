<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Factory;

use Psr\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Domain\Controller\AddUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\DeleteUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\GetUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\GetUsersAction;
use SlayerBirden\DataFlowServer\Domain\Controller\UpdateUserAction;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback)
    {
        /** @var $app Application */
        $app = $callback();

        $app->get('/user/{id:\d+}', [
            GetUserAction::class
        ], 'get_user');

        $app->get('/users', [
            GetUsersAction::class
        ], 'get_users');

        $app->post('/user', [
            BodyParamsMiddleware::class,
            AddUserAction::class
        ], 'add_user');

        $app->put('/user/{id:\d+}', [
            BodyParamsMiddleware::class,
            UpdateUserAction::class
        ], 'update_user');

        $app->delete('/user/{id:\d+}', [
            DeleteUserAction::class
        ], 'delete_user');

        return $app;
    }
}
