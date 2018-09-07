<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;

final class SimpleRegistry implements EntityManagerRegistry
{
    const DEFAULT_CONNECTION_NAME = 'default';
    const DEFAULT_MANAGER_NAME = 'default';

    /**
     * @var Connection[]
     */
    private $connections = [];
    /**
     * @var EntityManagerInterface[]
     */
    private $managers = [];
    /**
     * Custom Entity=>Manager config
     * @var array
     */
    private $entityManagerMapping = [];

    public function __construct(
        array $connections,
        array $managers,
        array $entityManagerMapping = []
    ) {
        $this->connections = $connections;
        $this->managers = $managers;
        $this->entityManagerMapping = $entityManagerMapping;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultConnectionName(): string
    {
        return self::DEFAULT_CONNECTION_NAME;
    }

    /**
     * @inheritdoc
     */
    public function getConnection($name = null): Connection
    {
        if ($name === null) {
            $name = $this->getDefaultConnectionName();
        }

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        throw new \InvalidArgumentException(sprintf('Could not find Doctrine connection with name %s', $name));
    }

    /**
     * {@inheritdoc}
     * @return Connection[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * @inheritdoc
     */
    public function getConnectionNames(): array
    {
        return array_keys($this->connections);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultManagerName(): string
    {
        return self::DEFAULT_MANAGER_NAME;
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\ORM\ORMException
     */
    public function getManager($name = null): EntityManagerInterface
    {
        if ($name === null) {
            $name = $this->getDefaultManagerName();
        }

        if (isset($this->managers[$name])) {
            $manager = $this->managers[$name];
            if (!$manager->isOpen()) {
                return $this->resetManager($name);
            }
            return $manager;
        }

        throw new \InvalidArgumentException(sprintf('Could not find Doctrine manager with name %s', $name));
    }

    /**
     * {@inheritdoc}
     * @return EntityManagerInterface[]
     */
    public function getManagers(): array
    {
        return $this->managers;
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\ORM\ORMException
     */
    public function resetManager($name = null): EntityManagerInterface
    {
        if ($name === null) {
            $name = $this->getDefaultManagerName();
        }

        if (!isset($this->managers[$name])) {
            throw new \InvalidArgumentException(sprintf('Could not find Doctrine manager with name %s', $name));
        }

        $manager = $this->managers[$name];
        $this->managers[$name] = EntityManager::create(
            $manager->getConnection(),
            $manager->getConfiguration(),
            $manager->getEventManager()
        );

        return $this->getManager($name);
    }

    /**
     * @inheritdoc
     * @unsupported
     */
    public function getAliasNamespace($alias): string
    {
        return $alias;
    }

    /**
     * @inheritdoc
     */
    public function getManagerNames(): array
    {
        return array_keys($this->managers);
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\ORM\ORMException
     */
    public function getRepository($persistentObject, $persistentManagerName = null): ObjectRepository
    {
        if ($persistentManagerName !== null) {
            $manager = $this->getManager($persistentManagerName);
        } else {
            $manager = $this->getManagerForClass($persistentObject);
        }
        return $manager->getRepository($persistentObject);
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\ORM\ORMException
     */
    public function getManagerForClass($class): EntityManagerInterface
    {
        $name = $this->entityManagerMapping[$class] ?? $this->getDefaultManagerName();

        return $this->getManager($name);
    }
}
