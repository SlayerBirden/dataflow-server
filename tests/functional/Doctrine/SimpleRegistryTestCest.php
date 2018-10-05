<?php
declare(strict_types=1);

namespace codecept\Doctrine;

use codecept\FunctionalTester;
use codecept\Helper\ZendExpressive3;
use Doctrine\DBAL\Driver\Connection;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;

class SimpleRegistryTestCest
{
    /**
     * @var ZendExpressive3
     */
    private $expressive;
    /**
     * @var EntityManagerRegistry
     */
    private $registry;

    public function _inject(ZendExpressive3 $expressive)
    {
        $this->expressive = $expressive;
    }

    public function _before(FunctionalTester $I)
    {
        $this->registry = $this->expressive->container->get(EntityManagerRegistry::class);
    }

    public function testReset(FunctionalTester $I)
    {
        $manager = $this->registry->getManager();
        $I->assertTrue($manager->isOpen());

        $manager->close(); // close manager
        $I->assertFalse($manager->isOpen());

        $newManager = $this->registry->getManager();
        $I->assertTrue($newManager->isOpen());
    }

    public function testGetConnection(FunctionalTester $I)
    {
        $connection = $this->registry->getConnection();
        $I->assertInstanceOf(Connection::class, $connection);
    }

    public function testGetConnections(FunctionalTester $I)
    {
        $connections = $this->registry->getConnections();
        $I->assertNotEmpty($connections);
    }

    public function testGetManagers(FunctionalTester $I)
    {
        $managers = $this->registry->getManagers();
        $I->assertNotEmpty($managers);
    }

    public function testGetManagerWrongName(FunctionalTester $I)
    {
        $I->expectException(
            new \InvalidArgumentException('Could not find Doctrine manager with name bar123'),
            function () {
                $this->registry->getManager('bar123');
            }
        );
    }
}
