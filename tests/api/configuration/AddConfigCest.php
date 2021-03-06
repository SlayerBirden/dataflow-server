<?php

namespace codecept\configuration;

use codecept\ApiTester;
use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\ObscuredStrategy;

class AddConfigCest
{
    public function addConfiguration(ApiTester $I)
    {
        $I->wantTo('create db configuration');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/config', [
            'title' => 'Test config',
            'url' => 'test_url',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'data' => [
                'configuration' => [
                    'title' => 'Test config',
                    'url' => 'test_url',
                ]
            ]
        ]);
    }

    public function addIncompleteConfig(ApiTester $I)
    {
        $I->wantTo('create incomplete db configuration');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/config', [
            'title' => 'Test config',
            'dbname' => 'test',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function addCompleteNonUrlConfig(ApiTester $I)
    {
        $I->wantTo('create complete non url db configuration');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/config', [
            'title' => 'Test config',
            'dbname' => 'test',
            'user' => 'test-user',
            'password' => 'test-pwd',
            'port' => '3306',
            'host' => 'localhost',
            'driver' => 'pdo_mysql'
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'data' => [
                'configuration' => [
                    'title' => 'Test config',
                    'dbname' => 'test',
                    'user' => 'test-user',
                    'password' => ObscuredStrategy::OBSCURED_STRING,
                    'port' => '3306',
                    'host' => 'localhost',
                    'driver' => 'pdo_mysql'
                ]
            ]
        ]);
    }

    public function mutateExitingRecord(ApiTester $I): void
    {
        $I->wantTo('attempt to mutate existing record by providing ID');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/config', [
            'title' => 'Test config',
            'url' => 'test_url',
        ]);

        $entities = $I->grabEntitiesFromRepository(DbConfiguration::class);
        $lastId = (end($entities))->getId();

        $I->sendPOST('/config', [
            'id' => $lastId,
            'title' => 'Test config infected',
            'url' => 'test_url_compromised',
        ]);

        $entities = $I->grabEntitiesFromRepository(DbConfiguration::class);
        $newLastId = (end($entities))->getId();

        $I->assertNotEquals($lastId, $newLastId);
    }
}
