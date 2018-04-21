<?php
declare(strict_types=1);

namespace Codeception\Module;

use Codeception\TestInterface;
use Doctrine\ORM\Tools\SchemaTool;

class CleanDoctrine2 extends Doctrine2
{
    protected $dependencyMessage = <<<EOF
Set a dependent module:

modules:
    enabled:
        - CleanDoctrine2:
            depends: ZendExpressive3
EOF;

    /**
     * @inheritdoc
     * @param TestInterface $test
     * @throws \Codeception\Exception\ModuleConfigException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    public function _before(TestInterface $test)
    {
        $this->retrieveEntityManager();
        if ($this->config['cleanup']) {
            $schemaManager = $this->em->getConnection()->getSchemaManager();
            $currentSchema = $schemaManager->createSchema();

            $metadatas = $this->em->getMetadataFactory()->getAllMetadata();
            $expectedSchema = (new SchemaTool($this->em))->getSchemaFromMetadata($metadatas);

            $sql = $currentSchema->getMigrateToSql($expectedSchema, $this->em->getConnection()->getDatabasePlatform());
            array_walk($sql, function ($script) {
                $this->em->getConnection()->executeUpdate($script);
            });

            $this->debugSection('Database', 'Database Created');
        }
    }

    /**
     * @inheritdoc
     * @throws \Codeception\Exception\ModuleConfigException
     */
    public function _after(TestInterface $test)
    {
        $this->retrieveEntityManager();
        if ($this->config['cleanup']) {
            $metadatas = $this->em->getMetadataFactory()->getAllMetadata();
            (new SchemaTool($this->em))->dropSchema($metadatas);

            $this->debugSection('Database', 'Database Is Dropped!');
        }

        $this->clean();
        $this->em->getConnection()->close();
    }
}
