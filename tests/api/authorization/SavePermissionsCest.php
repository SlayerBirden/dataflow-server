<?php

namespace codecept\authorization;

use codecept\ApiTester;
use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Authorization\Entities\History;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class SavePermissionsCest
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @param ApiTester $I
     * @throws \Exception
     */
    public function _before(ApiTester $I)
    {
        $this->userId = $I->haveInRepository(User::class, [
            'first' => 'Tester2',
            'last' => 'Tester2',
            'email' => 'test2@example.com',
        ]);

        $user = $I->grabEntityFromRepository(User::class, ['id' => $this->userId]);
        $resources = [
            'get_tmp_token',
            'save_permissions',
        ];
        foreach ($resources as $key => $resource) {
            $I->haveInRepository(Permission::class, [
                'id' => ++$key,
                'user' => $user,
                'resource' => $resource,
            ]);
        }
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettmptoken/' . $this->userId, [
            'resources' => [
                'save_permissions'
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $tmpToken = $I->grabDataFromResponseByJsonPath('data.token.token')[0];

        $I->amBearerAuthenticated($tmpToken);
    }

    public function savePermissionsSuccess(ApiTester $I)
    {
        $I->wantTo('successfully save permissions');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('/permissions/' . $this->userId, [
            'resources' => [
                'save_permissions',
                'get_tmp_token',
                'create_password',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['resource' => 'create_password']);
        $I->seeResponseContainsJson(['resource' => 'get_tmp_token']);
        $I->seeResponseContainsJson(['resource' => 'create_password']);
    }

    public function savePermissionsNoChanges(ApiTester $I)
    {
        $I->wantTo('re-save existing permissions');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('/permissions/' . $this->userId, [
            'resources' => [
                'get_tmp_token',
                'save_permissions',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['permissions' => []]);
    }

    public function setForNonExistingResources(ApiTester $I)
    {
        $I->wantTo('set permissions for non existing resources');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('/permissions/' . $this->userId, [
            'resources' => [
                'i_do_not_exist',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['validation' => [
            'field' => 'resources'
        ]]);
    }

    public function tryToSendWithNoResource(ApiTester $I)
    {
        $I->wantTo('send request with no resources');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('/permissions/' . $this->userId, []);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['validation' => [
            'field' => 'resources'
        ]]);
    }

    public function countHistoryChanges(ApiTester $I)
    {
        $I->wantTo('count history changes');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('/permissions/' . $this->userId, [
            'resources' => [],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        // remove 2
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('/permissions/' . $this->userId, [
            'resources' => [
                'save_permissions',
                'get_tmp_token',
                'create_password',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        // add 3
        $historyRecords = $I->grabEntitiesFromRepository(History::class);
        $I->assertCount(5, $historyRecords);
    }
}
