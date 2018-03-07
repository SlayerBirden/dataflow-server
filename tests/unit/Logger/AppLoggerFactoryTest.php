<?php

use Monolog\Handler\NoopHandler;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceManager;

class AppLoggerFactoryTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testHandlers()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', [
            'logger' => [
                'handlers' => [
                    new NoopHandler()
                ],
            ],
        ]);

        $loggerFactory = new \SlayerBirden\DataFlowServer\Logger\AppLoggerFactory();

        $logger = $loggerFactory($serviceManager);

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
    }

    public function testServiceHandlers()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', [
            'logger' => [
                'handlers' => [
                    NoopHandler::class
                ],
            ],
            'dependencies' => [
                'factories' => [
                    NoopHandler::class => InvokableFactory::class
                ],
            ],
        ]);

        $loggerFactory = new \SlayerBirden\DataFlowServer\Logger\AppLoggerFactory();

        $logger = $loggerFactory($serviceManager);

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
    }
}
