<?php
declare(strict_types=1);
/** @var ContainerInterface $container */

use Psr\Container\ContainerInterface;

chdir(dirname(__DIR__));

$container = require __DIR__ . "/container.php";

$em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($em);
