<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Service;

use SlayerBirden\DataFlowServer\Authorization\ResourceManagerInterface;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteCollector;

class ResourceManager implements ResourceManagerInterface
{
    /**
     * @var RouteCollector
     */
    private $routeCollector;

    public function __construct(RouteCollector $routeCollector)
    {
        $this->routeCollector = $routeCollector;
    }

    /**
     * @inheritdoc
     */
    public function getAllResources(): array
    {
        return array_map(function (Route $route) {
            return $route->getName();
        }, $this->routeCollector->getRoutes());
    }
}
