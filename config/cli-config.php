<?php
declare(strict_types=1);
/** @var ContainerInterface $container */

use Psr\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;

chdir(dirname(__DIR__));

$container = require __DIR__ . "/container.php";
/** @var EntityManagerRegistry $registry */
$registry = $container->get(EntityManagerRegistry::class);

try {
    return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($registry->getManager());
} catch (\Doctrine\ORM\ORMException $exception) {
    echo $exception->getMessage();
}
