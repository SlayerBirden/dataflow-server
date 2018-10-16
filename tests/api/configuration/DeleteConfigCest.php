<?php

namespace codecept\configuration;

use codecept\ApiTester;
use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class DeleteConfigCest
{
    public function _before(ApiTester $I)
    {
        $user = $I->grabEntityFromRepository(User::class, ['id' => 1]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'Test config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
    }

    public function deleteConfiguration(ApiTester $I)
    {
        $I->wantTo('delete db configuration');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE('/config/1');
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

    public function deleteNonExistingConfiguration(ApiTester $I)
    {
        $I->wantTo('delete non-existing db configuration');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE('/config/0');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'configuration' => null
            ]
        ]);
    }

    public function deleteSomeoneElsesConfiguration(ApiTester $I)
    {
        $userId = $I->haveInRepository(User::class, [
            'first' => 'Tester2',
            'last' => 'Tester2',
            'email' => 'test2@example.com',
        ]);
        $user = $I->grabEntityFromRepository(User::class, ['id' => $userId]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'Test config 2',
            'url' => 'sqlite:///data/db/db2.sqlite',
        ]);

        $I->wantTo('delete someone elses db configuration');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE('/config/2');
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeResponseContainsJson([
            'success' => false,
        ]);
    }
}
