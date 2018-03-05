<?php

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class GetConfigCest
{
    public function _before(ApiTester $I)
    {
        $I->haveInRepository(User::class, [
            'id' => 1,
            'first' => 'Tester',
            'last' => 'Tester',
        ]);
        $user = $I->grabEntityFromRepository(User::class, ['id' => 1]);
        $I->haveInRepository(DbConfiguration::class, [
            'id' => 1,
            'owner' => $user,
            'title' => 'Test config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
    }

    public function getConfiguration(ApiTester $I)
    {
        $I->wantTo('get db configuration');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/config/1');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'configuration' => [
                    'title' => 'Test config',
                ]
            ]
        ]);
    }

    public function getNonExistingConfiguration(ApiTester $I)
    {
        $I->wantTo('get none existing db configuration');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/config/0');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'configuration' => null
            ]
        ]);
    }
}
