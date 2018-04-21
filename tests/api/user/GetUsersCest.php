<?php

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class GetUsersCest
{
    public function _before(ApiTester $I)
    {
        foreach (range(2, 11) as $i) {
            $I->haveInRepository(User::class, [
                'id' => $i,
                'first' => 'Tester' . $i,
                'last' => 'Tester' . $i,
                'email' => 'test' . $i . '@example.com',
            ]);
        }
    }

    public function getAllUsers(ApiTester $I)
    {
        $I->wantTo('get all users');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/users');
        $I->seeResponseCodeIs(HttpCode::OK);
        $usersJson = [];
        foreach (range(2, 11) as $i) {
            $usersJson[] = [
                'email' => 'test' . $i . '@example.com',
            ];
        }
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'users' => $usersJson,
                'count' => 11,
            ]
        ]);
    }

    public function getSecondPageUsers(ApiTester $I)
    {
        $I->wantTo('get second page of users');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/users?p=2');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'users' => [
                    [
                        'email' => 'test11@example.com',
                    ],
                ],
                'count' => 11,
            ]
        ]);
    }

    public function getFilteredUsers(ApiTester $I)
    {
        $I->wantTo('get users filtered by test1');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/users?f[email]=test1');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'users' => [
                    [
                        'email' => 'test1@example.com',
                    ],
                    [
                        'email' => 'test10@example.com',
                    ],
                    [
                        'email' => 'test11@example.com',
                    ],
                ],
                'count' => 3,
            ]
        ]);
    }

    public function getSortedUsers(ApiTester $I)
    {
        $I->wantTo('get users sorted by id desc');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/users?s[id]=desc');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'users' => [
                    [
                        'email' => 'test11@example.com',
                    ],
                ],
                'count' => 11,
            ]
        ]);
    }

    public function getNoResultsFilterUsers(ApiTester $I)
    {
        $I->wantTo('attempt to get users with no resulsts filters');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/users?f[email]=bla');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'users' => [],
                'count' => 0,
            ]
        ]);
    }

    public function getWrongFilterUsers(ApiTester $I)
    {
        $I->wantTo('attempt to get users with wrong filters');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/users?f[age]=30');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'users' => [],
                'count' => 0,
            ]
        ]);
    }
}
