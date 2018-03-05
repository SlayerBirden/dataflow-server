<?php
declare(strict_types=1);

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\Connector\ZendExpressive as ZendExpressiveConnector;

class ZendExpressive3 extends ZendExpressive
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
}
