<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Authentication\Controller\CreatePasswordAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\GenerateTemporaryTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\GetTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\InvalidateTokenAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\InvalidateTokensAction;
use SlayerBirden\DataFlowServer\Authentication\Controller\UpdatePasswordAction;
use SlayerBirden\DataFlowServer\Authentication\Middleware\ActivePasswordMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Middleware\PasswordConfirmationMiddleware;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Domain\Middleware\SetOwnerMiddleware;
use SlayerBirden\DataFlowServer\Domain\Middleware\ValidateOwnerMiddleware;
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
            '/gettmptoken/{id:\d+}',
            [
                TokenMiddleware::class,
                'UserResourceMiddleware',
                BodyParamsMiddleware::class,
                GenerateTemporaryTokenAction::class,
            ],
            'get_tmp_token'
        );

        $app->post(
            '/password',
            [
                TokenMiddleware::class,
                BodyParamsMiddleware::class,
                ActivePasswordMiddleware::class,
                SetOwnerMiddleware::class,
                CreatePasswordAction::class,
            ],
            'create_password'
        );

        $app->post(
            '/gettoken',
            [
                BodyParamsMiddleware::class,
                GetTokenAction::class,
            ],
            'get_token'
        );

        $app->post(
            '/invalidatetoken/{id:\d+}',
            [
                TokenMiddleware::class,
                'TokenResourceMiddleware',
                ValidateOwnerMiddleware::class,
                InvalidateTokenAction::class,
            ],
            'invalidate_token'
        );

        $app->post(
            '/invalidatetokens',
            [
                TokenMiddleware::class,
                BodyParamsMiddleware::class,
                InvalidateTokensAction::class,
            ],
            'invalidate_tokens'
        );

        $app->post(
            '/updatepassword',
            [
                TokenMiddleware::class,
                BodyParamsMiddleware::class,
                ValidateOwnerMiddleware::class,
                PasswordConfirmationMiddleware::class,
                SetOwnerMiddleware::class,
                UpdatePasswordAction::class,
            ],
            'update_password'
        );

        return $app;
    }
}
