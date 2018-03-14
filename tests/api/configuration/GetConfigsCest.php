<?php

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class GetConfigsCest
{
    public function _before(ApiTester $I)
    {
        $I->haveInRepository(User::class, [
            'id' => 1,
            'first' => 'Tester',
            'last' => 'Tester',
            'email' => 'test@example.com',
        ]);

        $user = $I->grabEntityFromRepository(User::class, ['id' => 1]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'sqlite config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'mysql config',
            'url' => 'mysql://test-user:test-pwd@mysql/test',
        ]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'project 1 config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'project 2 config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'project 3 config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'project 4 config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'project 5 config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'project 6 config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'project 7 config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'project 8 config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
        $I->haveInRepository(DbConfiguration::class, [
            'owner' => $user,
            'title' => 'project 9 config',
            'url' => 'sqlite:///data/db/db.sqlite',
        ]);
    }

    public function getAllConfigurations(ApiTester $I)
    {
        $I->wantTo('get all db configurations');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/configs');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'configurations' => [
                    [
                        'title' => 'sqlite config',
                    ],
                    [
                        'title' => 'mysql config',
                    ],
                    [
                        'title' => 'project 1 config',
                    ],
                    [
                        'title' => 'project 2 config',
                    ],
                    [
                        'title' => 'project 3 config',
                    ],
                    [
                        'title' => 'project 4 config',
                    ],
                    [
                        'title' => 'project 5 config',
                    ],
                    [
                        'title' => 'project 6 config',
                    ],
                    [
                        'title' => 'project 7 config',
                    ],
                    [
                        'title' => 'project 8 config',
                    ],
                    [
                        'title' => 'project 9 config',
                    ],
                ],
                'count' => 11,
            ]
        ]);
    }

    public function getSecondPageConfigurations(ApiTester $I)
    {
        $I->wantTo('get second page of db configurations');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/configs?p=2');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'configurations' => [
                    [
                        'title' => 'project 9 config',
                    ],
                ],
                'count' => 11,
            ]
        ]);
    }

    public function getFilteredConfigurations(ApiTester $I)
    {
        $I->wantTo('get configurations filtered by mysql');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/configs?f[title]=mysql');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'configurations' => [
                    [
                        'title' => 'mysql config',
                    ],
                ],
                'count' => 1,
            ]
        ]);
    }

    public function getSortedConfigurations(ApiTester $I)
    {
        $I->wantTo('get configurations sorted by title asc');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/configs?s[title]=asc');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'configurations' => [
                    [
                        'title' => 'mysql config',
                    ],
                ],
                'count' => 11,
            ]
        ]);
    }

    public function getNoResultsFilterConfigurations(ApiTester $I)
    {
        $I->wantTo('attempt to get configurations with not matching filter');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/configs?f[title]=bla');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'configurations' => [],
                'count' => 0,
            ]
        ]);
    }
}
