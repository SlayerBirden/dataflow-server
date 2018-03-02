<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Psr\Container\ContainerInterface;

class EntityManagerFactory
{
    /**
     * @param ContainerInterface $container
     * @return EntityManager
     * @throws \Doctrine\ORM\ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EntityManager
    {
        $config = $container->has('config') ? $container->get('config') : [];

        if (!isset($config['doctrine'])) {
            throw new Exception\MissingDoctrineConfigException(
                'Doctrine configuration is missing'
            );
        }

        $doctrineAutoloadConfig = $config['doctrine'];
        $paths = $doctrineAutoloadConfig['paths'] ?? [];
        $devMode = $doctrineAutoloadConfig['dev_mode'] ?? false;

        $conn = $config['db'] ?? [];

        // get "base" config
        $doctrineConfiguration = Setup::createAnnotationMetadataConfiguration(
            $paths,
            $devMode,
            null,
            null,
            false
        );

        $excludePaths = $doctrineAutoloadConfig['excludePaths'] ?? [];
        if (!empty($excludePaths)) {
            $annotation = $doctrineConfiguration->getMetadataDriverImpl();
            if ($annotation instanceof \Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver) {
                $annotation->addExcludePaths($excludePaths);
            }
        }

        // if explicit config is set - add it
        $conf = $doctrineAutoloadConfig['configuration'] ?? [];
        $this->populateConfig($conf, $doctrineConfiguration, $container);

        // obtaining the entity manager
        $entityManager = EntityManager::create($conn, $doctrineConfiguration);

        return $entityManager;
    }

    /**
     * @param array $configuration
     * @param Configuration $configurationObject
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws Exception\InvalidArgumentDoctrineConfigException
     */
    private function populateConfig(
        array $configuration,
        Configuration $configurationObject,
        ContainerInterface $container
    ): void {
        foreach ($configuration as $key => $value) {
            $method = $this->guessMethod($key, $configurationObject);
            try {
                $configurationObject->$method($value);
            } catch (\TypeError $error) {
                // attempt to get from Container
                if ($container->has($value)) {
                    $configurationObject->$method($container->get($value));
                } else {
                    throw new Exception\InvalidArgumentDoctrineConfigException(
                        'Invalid argument in Doctrine config',
                        0,
                        $error
                    );
                }
            }
        }
    }

    /**
     * @param string $key
     * @param $object // any object
     * @return string
     * @throws Exception\InvalidArgumentDoctrineConfigException
     */
    private function guessMethod(string $key, $object): string
    {
        $setter = 'set' . Inflector::camelize($key);
        $adder = 'add' . Inflector::camelize($key);

        if (method_exists($object, $setter)) {
            return $setter;
        }

        if (method_exists($adder, $setter)) {
            return $setter;
        }

        throw new Exception\InvalidArgumentDoctrineConfigException(
            sprintf('Invalid key in Doctrine config: %s', $key)
        );
    }
}
