<?php
declare(strict_types=1);

namespace codecept\Helper;

use Codeception\Configuration;
use Codeception\Lib\Connector\ZendExpressive as ZendExpressiveConnector;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;

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

    /**
     * @return \Doctrine\ORM\EntityManagerInterface|mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function _getEntityManager()
    {
        if (!$this->container->has(EntityManagerRegistry::class)) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Service EntityManagerRegistry is not available in container"
            );
        }
        /** @var EntityManagerRegistry $registry */
        $registry = $this->container->get(EntityManagerRegistry::class);

        return $registry->getManager();
    }
}
