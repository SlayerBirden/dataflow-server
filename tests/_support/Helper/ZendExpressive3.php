<?php
declare(strict_types=1);

namespace codecept\Helper;

use Codeception\Configuration;
use Codeception\Lib\Connector\ZendExpressive as ZendExpressiveConnector;
use Doctrine\Common\Persistence\ManagerRegistry;

class ZendExpressive3 extends \Codeception\Module\ZendExpressive
{
    public function _initialize()
    {
        $cwd = getcwd();
        $projectDir = Configuration::projectDir();
        chdir($projectDir);
        $this->container = require $projectDir . $this->config['container'];
        $app = $this->container->get(\Zend\Expressive\Application::class);

        $pipelineFile = $projectDir . 'config/pipeline.php';
        if (file_exists($pipelineFile)) {
            require $pipelineFile;
        }
        $routesFile = $projectDir . 'config/routes.php';
        if (file_exists($routesFile)) {
            require $routesFile;
        }
        chdir($cwd);

        $this->application = $app;
        // remove the init method, since emitter is not part of ze3
        $this->responseCollector = new ZendExpressiveConnector\ResponseCollector;
    }

    public function _getEntityManager()
    {
        if (!$this->container->has(ManagerRegistry::class)) {
            throw new \PHPUnit\Framework\AssertionFailedError("Service ManagerRegistry is not available in container");
        }
        /** @var ManagerRegistry $registry */
        $registry = $this->container->get(ManagerRegistry::class);

        return $registry->getManager();
    }
}
