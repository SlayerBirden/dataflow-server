<?php

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class UpdateConfigCest
{
    public function _before(ApiTester $I)
    {
        $I->haveInRepository(User::class, [
            'id' => 1,
            'first' => 'Tester',
            'last' => 'Tester',
            'email' => 'test@example.com',
        ]);

        $user = $I->grabEntityFromRepository(User::class, ['id' => 1]);
        $I->haveInRepository(DbConfiguration::class, [
            'id' => 1,
            'owner' => $user,
            'title' => 'sqlite config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
    }

    public function updateConfiguration(ApiTester $I)
    {
        $I->wantTo('update db configuration');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/config/1', [
            'title' => 'sqlite updated',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'configuration' => [
                    'title' => 'sqlite updated',
                    'url' => 'sqlite:///data/db/db.sqlite',
                ]
            ]
        ]);
    }

    public function updateNonExistingConfig(ApiTester $I)
    {
        $I->wantTo('update non existing db configuration');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/config/2', [
            'title' => 'Test config',
        ]);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'configuration' => null
            ]
        ]);
    }
}