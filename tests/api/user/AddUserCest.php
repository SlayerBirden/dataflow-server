<?php

namespace codecept;

use Codeception\Util\HttpCode;

class AddUserCest
{
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

    public function addIncompleteUser(ApiTester $I)
    {
        $I->wantTo('create user with incomplete data');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/user', [
            'last' => 'User',
            'email' => 'test@example.com',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'validation' => [
                    [
                        'field' => 'first',
                    ]
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
            'email' => 'test1@example.com',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
        ]);
    }
}
