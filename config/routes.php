<?php
declare(strict_types=1);

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

$container = require __DIR__ . '/container.php';
/** @var \Zend\Expressive\Application $app */
$app = $container->get(\Zend\Expressive\Application::class);

$app->get('/', function () {
    $response = new \Zend\Diactoros\Response\JsonResponse([
        'data' => 'Welcome to the DataFlow server!'
    ]);

    return $response;
});

/** @var \Doctrine\ORM\EntityManagerInterface $em */
$em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);

$app->get('/transact', function () use ($em) {

    $em->beginTransaction();
    $em->getConnection()->query("insert into users set first='test1', last='test2'");
    $em->rollback();

    $response = new \Zend\Diactoros\Response\JsonResponse([]);

    return $response;
});
