<?php
declare(strict_types=1);

use SlayerBirden\DataFlowServer\Extractor\RecursiveEntitiesExtractor;
use Zend\Expressive\Application;
use Zend\Expressive\Container;
use Zend\Expressive\Helper;
use Zend\Expressive\Middleware;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;

return [
    'dependencies' => [
        'invokables' => [
            Helper\ServerUrlHelper::class => Helper\ServerUrlHelper::class,
            \Zend\Hydrator\ClassMethods::class => \Zend\Hydrator\ClassMethods::class,
        ],
        'factories' => [
            Application::class => Container\ApplicationFactory::class,
            Helper\ServerUrlMiddleware::class => Helper\ServerUrlMiddlewareFactory::class,
            Helper\UrlHelper::class => Helper\UrlHelperFactory::class,
            Helper\UrlHelperMiddleware::class => Helper\UrlHelperMiddlewareFactory::class,
            Zend\Stratigility\Middleware\ErrorHandler::class => Container\ErrorHandlerFactory::class,
            Middleware\ErrorResponseGenerator::class => Container\ErrorResponseGeneratorFactory::class,
        ],
        'abstract_factories' => [
            ConfigAbstractFactory::class
        ],
    ],
];
