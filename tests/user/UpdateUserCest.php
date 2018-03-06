<?php

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class UpdateUserCest
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

    public function updateUser(ApiTester $I)
    {
        $I->wantTo('update user');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/user/1', [
            'first' => 'Bob',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'user' => [
                    'first' => 'Bob',
                    'last' => 'Tester',
                    'email' => 'test@example.com',
                ]
            ]
        ]);
    }

    public function updateNonExistingUser(ApiTester $I)
    {
        $I->wantTo('update non existing user');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/user/2', [
            'first' => 'John',
        ]);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'user' => null
            ]
        ]);
    }
}
