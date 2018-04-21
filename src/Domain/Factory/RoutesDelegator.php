<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Domain\Controller\AddUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\DeleteUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\GetUserAction;
use SlayerBirden\DataFlowServer\Domain\Controller\GetUsersAction;
use SlayerBirden\DataFlowServer\Domain\Controller\UpdateUserAction;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

class RoutesDelegator implements DelegatorFactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        array $options = null
    ): Application {
        /** @var Application $app */
        $app = $callback();

        $app->get('/user/{id:\d+}', [
            TokenMiddleware::class,
            GetUserAction::class
        ], 'get_user');

        $app->get('/users', [
            TokenMiddleware::class,
            GetUsersAction::class
        ], 'get_users');

        $app->post('/user', [
            TokenMiddleware::class,
            BodyParamsMiddleware::class,
            AddUserAction::class
        ], 'add_user');

        $app->put('/user/{id:\d+}', [
            TokenMiddleware::class,
            BodyParamsMiddleware::class,
            UpdateUserAction::class
        ], 'update_user');

        $app->delete('/user/{id:\d+}', [
            TokenMiddleware::class,
            DeleteUserAction::class
        ], 'delete_user');

        return $app;
    }
}
