<?php

namespace codecept\authorization;

use codecept\ApiTester;
use Codeception\Util\HttpCode;

class GetResourcesCest
{
    public function getResourcesSuccess(ApiTester $I)
    {
        $I->wantTo('Get resources');
        $I->sendGet('/resources');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.data.resources[*]');
    }
}
