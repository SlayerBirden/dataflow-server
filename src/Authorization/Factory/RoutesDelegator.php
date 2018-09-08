<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Authorization\Controller\GetPermissionHistoryAction;
use SlayerBirden\DataFlowServer\Authorization\Controller\GetResourcesAction;
use SlayerBirden\DataFlowServer\Authorization\Controller\SavePermissionsAction;
use SlayerBirden\DataFlowServer\Domain\Middleware\SetOwnerMiddleware;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

final class RoutesDelegator implements DelegatorFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        array $options = null
    ): Application {
        /** @var $app Application */
        $app = $callback();

        $app->get('/resources', [
            TokenMiddleware::class,
            GetResourcesAction::class
        ], 'get_resources');

        $app->put('/permissions/{id:\d+}', [
            TokenMiddleware::class,
            'UserResourceMiddleware',
            BodyParamsMiddleware::class,
            SetOwnerMiddleware::class,
            SavePermissionsAction::class
        ], 'save_permissions');

        $app->get('/history', [
            TokenMiddleware::class,
            GetPermissionHistoryAction::class
        ], 'get_permission_history');

        return $app;
    }
}
