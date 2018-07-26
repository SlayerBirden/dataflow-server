<?php
declare(strict_types=1);

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class GenerateTmpTokenCest
{
    /**
     * @var int
     */
    private $userId;

    public function _before(ApiTester $I)
    {
        $this->userId = $I->haveInRepository(User::class, [
            'first' => 'Tester2',
            'last' => 'Tester2',
            'email' => 'test2@example.com',
        ]);

        $user = $I->grabEntityFromRepository(User::class, ['id' => $this->userId]);
        $resources = [
            'create_password',
            'get_tmp_token',
        ];
        foreach ($resources as $key => $resource) {
            $I->haveInRepository(Permission::class, [
                'id' => ++$key,
                'user' => $user,
                'resource' => $resource,
            ]);
        }
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function createTmpTokenSuccess(ApiTester $I)
    {
        $I->wantTo('create tmp token');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettmptoken/' . (string)$this->userId, [
            'resources' => [
                'create_password'
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'token' => [
                    'owner' => [
                        'email' => 'test2@example.com',
                    ],
                    'active' => 1,
                ],
            ],
        ]);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function createTmpTokenForNonExistingUser(ApiTester $I)
    {
        $I->wantTo('create tmp token for non existing user');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettmptoken/' . (string)($this->userId + 100), [
            'resources' => [
                'create_password'
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'success' => false,
        ]);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function createTmpTokenNotPermitted(ApiTester $I)
    {
        $I->wantTo('create tmp token for resource without granted permission');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettmptoken/' . (string)$this->userId, [
            'resources' => [
                'update_password'
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
        ]);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function createTmpTokenValidationError(ApiTester $I)
    {
        $I->wantTo('create tmp token wrong input');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettmptoken/' . (string)$this->userId, [
            'bar' => 'baz',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'validation' => [
                    [
                        'field' => 'resources'
                    ]
                ]
            ]
        ]);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function createTmpTokenValidationErrorNonExistingResource(ApiTester $I)
    {
        $I->wantTo('create tmp token for non existing resource');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettmptoken/' . (string)$this->userId, [
            'resources' => [
                'bar'
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
        ]);
    }
}
