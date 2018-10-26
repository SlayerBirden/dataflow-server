<?php
declare(strict_types=1);

use SlayerBirden\DataFlowServer\Stdlib\ResponseFactory;

$container = require __DIR__ . '/container.php';
/** @var \Zend\Expressive\Application $app */
$app = $container->get(\Zend\Expressive\Application::class);

$app->get('/', function () {
    $response = (new ResponseFactory())('Welcome to the DataFlow server!', 200);

    return $response;
}, 'root');
