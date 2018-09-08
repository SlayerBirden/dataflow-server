<?php

namespace codecept\authorization;

use codecept\ApiTester;
use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Authorization\Entities\History;
use SlayerBirden\DataFlowServer\Authorization\Service\HistoryManagement;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class GetPermissionHistoryCest
{
    /**
     * @param ApiTester $I
     * @throws \Exception
     */
    public function _before(ApiTester $I)
    {
        $user = $I->grabEntityFromRepository(User::class, ['id' => $I->getCurrentUserId()]);
        $I->haveInRepository(History::class, [
            'user' => $user,
            'owner' => $user,
            'resource' => 'awesome_sauce',
            'changeAction' => HistoryManagement::ACTION_REMOVE,
            'at' => new \DateTime(),
        ]);
    }

    public function getHistorySuccess(ApiTester $I)
    {
        $I->wantTo('Get permission history');
        $I->sendGet('/history');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.data.history[*]');
    }
}
