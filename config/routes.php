<?php
declare(strict_types=1);

$container = require __DIR__ . '/container.php';
/** @var \Zend\Expressive\Application $app */
$app = $container->get(\Zend\Expressive\Application::class);

$app->get('/', function () {
    $response = new \Zend\Diactoros\Response\JsonResponse([
        'data' => 'Welcome to the DataFlow server!'
    ]);

    return $response;
});
