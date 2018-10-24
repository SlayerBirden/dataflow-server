<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Factory;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Exception\InvalidArgumentDoctrineConfigException;
use SlayerBirden\DataFlowServer\Doctrine\Exception\MissingDoctrineConfigException;
use SlayerBirden\DataFlowServer\Doctrine\SimpleRegistry;
use Zend\ServiceManager\Factory\FactoryInterface;

final class ManagerRegistryFactory implements FactoryInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @inheritdoc
     * @throws \Doctrine\ORM\ORMException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ManagerRegistry
    {
        $config = $container->has('config') ? $container->get('config') : [];

        $connections = $this->getConnections($container, $config);

        if (!isset($config['doctrine'])) {
            throw new MissingDoctrineConfigException(
                'Doctrine configuration is missing'
            );
        }

        $doctrineAutoloadConfig = $config['doctrine'];
        $emMapping = $doctrineAutoloadConfig['entity_manager_mapping'] ?? [];

        if (!isset($doctrineAutoloadConfig['entity_managers'])) {
            throw new MissingDoctrineConfigException(
                'Doctrine configuration is missing entity managers'
            );
        }

        $entityManagersConfig = $doctrineAutoloadConfig['entity_managers'];

        $managers = [];
        foreach ($entityManagersConfig as $key => $emConfig) {
            $paths = $emConfig['paths'] ?? [];
            $devMode = $emConfig['dev_mode'] ?? false;

            // get "base" config
            $doctrineConfiguration = Setup::createAnnotationMetadataConfiguration(
                $paths,
                $devMode,
                null,
                null,
                false
            );

            $this->populateConfig($emConfig, $doctrineConfiguration, $container);

            $connectionName = $emConfig['connection'] ?? 'default';

            if (!isset($connections[$connectionName])) {
                throw new InvalidArgumentDoctrineConfigException(
                    sprintf('Can not find DB connection named %s', $connectionName)
                );
            }

            // obtaining the entity manager
            $managers[$key] = EntityManager::create(
                $connections[$connectionName],
                $doctrineConfiguration,
                $this->getEventManager($container, $doctrineAutoloadConfig)
            );
        }

        return new SimpleRegistry($connections, $managers, $emMapping);
    }

    private function populateConfig(
        array $configuration,
        Configuration $configurationObject,
        ContainerInterface $container
    ): void {
        if (isset($configuration['naming_strategy'])) {
            $configurationObject->setNamingStrategy($container->get($configuration['naming_strategy']));
        }
        if (isset($configuration['proxy_dir'])) {
            $configurationObject->setProxyDir($configuration['proxy_dir']);
        }
    }

    private function getEventManager(ContainerInterface $container, array $doctrineConfig): EventManager
    {
        if ($this->eventManager === null) {
            $this->eventManager = new EventManager();

            $subscribers = $doctrineConfig['subscribers'] ?? [];
            $listeners = $doctrineConfig['listeners'] ?? [];

            foreach ($subscribers as $subscriber) {
                $this->eventManager->addEventSubscriber($container->get($subscriber));
            }
            foreach ($listeners as $listener) {
                $this->eventManager->addEventListener(
                    $listener['events'] ?? [],
                    $container->get($listener['class'] ?? '')
                );
            }
        }

        return $this->eventManager;
    }

    private function getConnections(ContainerInterface $container, array $config): array
    {
        if (!isset($config['connections'])) {
            throw new MissingDoctrineConfigException(
                'DB Connections configuration is missing'
            );
        }

        return array_map(function (array $connection) use ($container, $config) {
            return DriverManager::getConnection(
                $connection,
                null,
                $this->getEventManager($container, $config['doctrine'] ?? [])
            );
        }, $config['connections']);
    }
}
