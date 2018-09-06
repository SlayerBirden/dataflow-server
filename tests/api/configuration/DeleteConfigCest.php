<?php

namespace codecept;

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class DeleteConfigCest
{
    public function _before(ApiTester $I)
    {
        $user = $I->grabEntityFromRepository(User::class, ['id' => 1]);
        $I->haveInRepository(DbConfiguration::class, [
            'id' => 1,
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
}
