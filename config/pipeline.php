<?php
declare(strict_types=1);

use Zend\Expressive\Flash\FlashMessageMiddleware;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Handler\NotFoundHandler;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Stratigility\Middleware\ErrorHandler;

$container = require __DIR__ . '/container.php';
/** @var \Zend\Expressive\Application $app */
$app = $container->get(\Zend\Expressive\Application::class);

$app->pipe(ErrorHandler::class);
$app->pipe(ServerUrlMiddleware::class);

$app->pipe(SessionMiddleware::class);
$app->pipe(FlashMessageMiddleware::class);

$app->pipe(\Zend\Expressive\Router\Middleware\RouteMiddleware::class);
$app->pipe(ImplicitHeadMiddleware::class);
$app->pipe(ImplicitOptionsMiddleware::class);
$app->pipe(UrlHelperMiddleware::class);

$app->pipe(\Zend\Expressive\Router\Middleware\DispatchMiddleware::class);
$app->pipe(NotFoundHandler::class);
