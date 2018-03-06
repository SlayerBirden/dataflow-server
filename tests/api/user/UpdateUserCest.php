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
        $I->haveInRepository(User::class, [
            'id' => 2,
            'first' => 'Tester2',
            'last' => 'Tester2',
            'email' => 'test2@example.com',
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
        $I->sendPUT('/user/3', [
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

    public function updateUserSetId(ApiTester $I)
    {
        $I->wantTo('update user and attempt to set id');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/user/1', [
            'id' => 2,
            'first' => 'John',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => 1,
                    'first' => 'John',
                    'last' => 'Tester',
                    'email' => 'test@example.com',
                ]
            ]
        ]);
    }

    public function updateUserInvalidEmail(ApiTester $I)
    {
        $I->wantTo('update user set invalid email');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/user/1', [
            'email' => 'testing',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'user' => null,
                'validation' => [
                    'field' => 'email',
                ]
            ]
        ]);
    }

    public function updateUserSetExistingEmail(ApiTester $I)
    {
        $I->wantTo('update user set existing email');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/user/1', [
            'email' => 'test2@example.com',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'user' => [
                    'first' => 'Tester',
                    'last' => 'Tester',
                    'email' => 'test2@example.com',
                ]
            ]
        ]);
    }
}
