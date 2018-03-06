<?php

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class GetUserCest
{
    public function _before(ApiTester $I)
    {
        $I->haveInRepository(User::class, [
            'id' => 1,
            'first' => 'Tester',
            'last' => 'Tester',
            'email' => 'test@example.com',
        ]);
    }

    public function getUser(ApiTester $I)
    {
        $I->wantTo('get user');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/user/1');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'user' => [
                    'email' => 'test@example.com',
                ]
            ]
        ]);
    }

    public function getNonExistingUser(ApiTester $I)
    {
        $I->wantTo('get none existing user');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/user/0');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'user' => null
            ]
        ]);
    }
}
