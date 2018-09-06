<?php
declare(strict_types=1);
/** @var ContainerInterface $container */

use Psr\Container\ContainerInterface;

chdir(dirname(__DIR__));

$container = require __DIR__ . "/container.php";

/** @var \Doctrine\Common\Persistence\ManagerRegistry $registry */
$registry = $container->get(\Doctrine\Common\Persistence\ManagerRegistry::class);

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($registry->getManager());
