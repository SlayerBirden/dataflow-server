<?php
declare(strict_types=1);

namespace DataFlow\Tests\Unit\Doctrine;

use SlayerBirden\DataFlowServer\Doctrine\EntityManagerFactory;

class EntityManagerFactoryTest extends \Codeception\Test\Unit
{
    /**
     * @expectedException \SlayerBirden\DataFlowServer\Doctrine\Exception\MissingDoctrineConfigException
     */
    public function testMissingDoctrineConfig()
    {
        $sm = new \Zend\ServiceManager\ServiceManager();

        $factory = new EntityManagerFactory();

        $factory($sm);
    }

    public function testConfigOption()
    {
        $sm = new \Zend\ServiceManager\ServiceManager();

        $sm->setService('config', [
            'doctrine' => [
                'configuration' => [
                    'proxy_dir' => 'path_to_proxy',
                    'filter' => [
                        'name',
                        'CoolNameFilter',
                    ]
                ],
            ],
            'db' => [
                'url' => 'sqlite:///data/db/db.sqlite',
            ],
        ]);

        $factory = new EntityManagerFactory();

        $em = $factory($sm);

        $this->assertEquals('path_to_proxy', $em->getConfiguration()->getProxyDir());
        $this->assertEquals('CoolNameFilter', $em->getConfiguration()->getFilterClassName('name'));
    }

    /**
     * @expectedException \SlayerBirden\DataFlowServer\Doctrine\Exception\InvalidArgumentDoctrineConfigException
     */
    public function testInvalidConfigOption()
    {
        $sm = new \Zend\ServiceManager\ServiceManager();

        $sm->setService('config', [
            'doctrine' => [
                'configuration' => [
                    'non_existing_option' => 'bar'
                ],
            ]
        ]);

        $factory = new EntityManagerFactory();

        $factory($sm);
    }

    /**
     * @expectedException \SlayerBirden\DataFlowServer\Doctrine\Exception\InvalidArgumentDoctrineConfigException
     */
    public function testInvalidConfigOptionType()
    {
        $sm = new \Zend\ServiceManager\ServiceManager();

        $sm->setService('config', [
            'doctrine' => [
                'configuration' => [
                    'metadata_driver_impl' => 'driver'
                ],
            ]
        ]);

        $factory = new EntityManagerFactory();

        $factory($sm);
    }


    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function testConfigOptionFromServiceManager()
    {
        $sm = new \Zend\ServiceManager\ServiceManager();

        $driver = $this->createMock(\Doctrine\Common\Persistence\Mapping\Driver\MappingDriver::class);

        $sm->setService('driver', $driver);

        $sm->setService('config', [
            'doctrine' => [
                'configuration' => [
                    'metadata_driver_impl' => 'driver'
                ],
            ],
            'db' => [
                'url' => 'sqlite:///data/db/db.sqlite',
            ],
        ]);

        $factory = new EntityManagerFactory();

        $em = $factory($sm);

        $this->assertInstanceOf(
            \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver::class,
            $em->getConfiguration()->getMetadataDriverImpl()
        );
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function testExcludedPaths()
    {
        $sm = new \Zend\ServiceManager\ServiceManager();

        $pathToExclude = [
            'path_to_tests',
            'path_to_logs',
        ];

        $sm->setService('config', [
            'doctrine' => [
                'excludePaths' => $pathToExclude,
            ],
            'db' => [
                'url' => 'sqlite:///data/db/db.sqlite',
            ],
        ]);

        $factory = new EntityManagerFactory();

        $em = $factory($sm);

        $driver = $em->getConfiguration()->getMetadataDriverImpl();
        if ($driver instanceof \Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver) {
            $this->assertEquals($pathToExclude, $driver->getExcludePaths());
        }
    }
}
