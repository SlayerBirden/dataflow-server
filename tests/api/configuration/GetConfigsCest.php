<?php

namespace codecept\configuration;

use codecept\ApiTester;
use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class GetConfigsCest
{
    public function _before(ApiTester $I)
    {
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
        for ($i = 1; $i < 10; $i++) {
            $I->haveInRepository(DbConfiguration::class, [
                'owner' => $user,
                'title' => sprintf('project %d config', $i),
                'url' => 'sqlite:///data/db/db.sqlite',
            ]);
        }
    }

    public function getAllConfigurations(ApiTester $I)
    {
        $I->wantTo('get all db configurations');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/configs?l=100');
        $I->seeResponseCodeIs(HttpCode::OK);
        $configs = [
            [
                'title' => 'sqlite config',
            ],
            [
                'title' => 'mysql config',
            ],
        ];
        $otherConfigs = [];
        for ($i = 1; $i < 10; $i++) {
            $otherConfigs[] = [
                'title' => sprintf('project %d config', $i),
            ];
        }
        $configs = array_merge($configs, $otherConfigs);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'configurations' => $configs,
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

    public function getWrongFilterUsers(ApiTester $I)
    {
        $I->wantTo('attempt to get configurations with wrong filters');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/configs?f[abracadabra]=30');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'configurations' => [],
                'count' => 0,
            ]
        ]);
    }
}
