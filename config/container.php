<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Zend\ServiceManager\ServiceManager;

if (isset($container) && $container instanceof ContainerInterface) {
    return $container;
}

// Load configuration
$config = require __DIR__ . '/config.php';

$dependencies = $config['dependencies'];
$dependencies['services']['config'] = $config;

// Build container
$container = new ServiceManager($dependencies);
return $container;
