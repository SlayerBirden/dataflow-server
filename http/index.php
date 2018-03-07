<?php
declare(strict_types=1);

chdir(dirname(__DIR__));

require_once 'vendor/autoload.php';

if (getenv('APP_MODE') === 'test') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    require 'c3.php';
}

call_user_func(function () {
    require 'config/pipeline.php';
    require 'config/routes.php';

    $container = require 'config/container.php';
    /** @var \Zend\Expressive\Application $app */
    $app = $container->get(\Zend\Expressive\Application::class);
    $app->run();
});
