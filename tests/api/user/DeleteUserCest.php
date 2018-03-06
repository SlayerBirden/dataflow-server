<?php

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class DeleteUserCest
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

    public function deleteUser(ApiTester $I)
    {
        $I->wantTo('delete user');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE('/user/1');
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

    public function deleteNonExistingUser(ApiTester $I)
    {
        $I->wantTo('delete none existing user');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE('/user/0');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'user' => null
            ]
        ]);
    }
}