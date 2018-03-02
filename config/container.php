<?php
declare(strict_types=1);

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

if (isset($container) && $container instanceof ContainerInterface) {
    return $container;
}

$container = new ServiceManager();

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container
$container = new ServiceManager();
(new Config($config['dependencies']))->configureServiceManager($container);

// Inject config
$container->setService('config', $config);

/**
 * @return ServiceManager
 */
return $container;
