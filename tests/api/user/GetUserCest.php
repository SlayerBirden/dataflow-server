<?php

namespace codecept\user;

use codecept\ApiTester;
use Codeception\Util\HttpCode;

class GetUserCest
{
    public function getUser(ApiTester $I)
    {
        $I->wantTo('get user');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/user/1');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'data' => [
                'user' => [
                    'email' => 'test1@example.com',
                ]
            ]
        ]);
    }

    public function getNonExistingUser(ApiTester $I)
    {
        $I->wantTo('get non-existing user');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/user/0');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'data' => [
                'user' => null
            ]
        ]);
    }
}
