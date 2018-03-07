<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Logger;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;

class AppLoggerFactory
{
    /**
     * @param ContainerInterface $container
     * @return Logger
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $loggerConfig = $config['logger'] ?? [];
        $handlersAdded = false;

        $logger = new Logger('app');

        if (!empty($loggerConfig)) {
            $handlers = $loggerConfig['handlers'] ?? [];
            if (!empty($handlers) && is_array($handlers)) {
                foreach ($handlers as $handler) {
                    if (is_object($handler) && $handler instanceof HandlerInterface) {
                        $logger->pushHandler($handler);
                        $handlersAdded = true;
                    } elseif (is_string($handler) && $container->has($handler)) {
                        $logger->pushHandler($container->get($handler));
                        $handlersAdded = true;
                    }
                }
            }
        }

        if (!$handlersAdded) {
            // add default handler
            $baseHandler = new StreamHandler('data/log/app.log');
            $logger->pushHandler($baseHandler);
        }

        return $logger;
    }
}
