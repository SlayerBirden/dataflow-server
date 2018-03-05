<?php

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class AddUserCest
{
    public function _before(ApiTester $I)
    {
        $I->haveInRepository(User::class, [
            'id' => 1,
            'first' => 'Tester',
            'last' => 'Tester',
            'email' => 'old_test@example.com',
        ]);
    }

    public function addUser(ApiTester $I)
    {
        $I->wantTo('create user');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/user', [
            'first' => 'Test',
            'last' => 'User',
            'email' => 'test@example.com',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'user' => [
                    'first' => 'Test',
                    'last' => 'User',
                    'email' => 'test@example.com',
                ]
            ]
        ]);
    }

    public function addUserWithWrongEmail(ApiTester $I)
    {
        $I->wantTo('create user with wrong email');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/user', [
            'first' => 'Test',
            'last' => 'User',
            'email' => 'wrong email',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'validation' => [
                    [
                        'field' => 'email',
                    ]
                ]
            ]
        ]);
    }

    public function addUserWithExistingEmail(ApiTester $I)
    {
        $I->wantTo('create user with existing email');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/user', [
            'first' => 'Test',
            'last' => 'User',
            'email' => 'old_test@example.com',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'user' => null
            ]
        ]);
    }
}
