<?php
declare(strict_types=1);

namespace codecept;

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class CreatePasswordCest
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
    public function createPasswordSuccess(ApiTester $I)
    {
        $I->wantTo('create password');
        $this->authForUser($I, $this->userId);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/password', [
            'password' => 'abra cadabra',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'password' => [
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
     * @param int $userId
     * @throws Exception
     */
    private function authForUser(ApiTester $I, int $userId)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettmptoken/' . $userId, [
            'resources' => [
                'create_password'
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $tmpToken = $I->grabDataFromResponseByJsonPath('data.token.token')[0];

        $I->amBearerAuthenticated($tmpToken);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function createPasswordNoPasswordProvided(ApiTester $I)
    {
        $I->wantTo('create password while not providing one');
        $this->authForUser($I, $this->userId);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/password', [
            'bar' => 'baz',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'password' => null,
                'validation' => [
                    'field' => 'password',
                ]
            ],
        ]);
    }

    /**
     * @param ApiTester $I
     * @throws \Exception
     */
    public function createPasswordUserAlreadyHasPassword(ApiTester $I)
    {
        $I->wantTo('create password for user already having one');
        $this->authForUser($I, $this->userId);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $user = $I->grabEntityFromRepository(User::class, ['id' => $this->userId]);
        $I->haveInRepository(Password::class, [
            'hash' => 'this is hash',
            'createdAt' => new \DateTime(),
            'due' => new \DateTime('+1 day'),
            'active' => 1,
            'owner' => $user,
        ]);
        $I->sendPOST('/password', [
            'password' => 'abra cadabra',
        ]);
        $I->seeResponseCodeIs(HttpCode::PRECONDITION_FAILED);
        $I->seeResponseContainsJson([
            'success' => false,
        ]);
    }

    /**
     * @param ApiTester $I
     * @throws \Exception
     */
    public function createPasswordException(ApiTester $I)
    {
        $I->wantTo('create password short password');
        $this->authForUser($I, $this->userId);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/password', [
            'password' => 'test123',
            'owner' => 'mr twister',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'password' => null,
                'validation' => [
                    [
                        'field' => 'password'
                    ]
                ],
            ],
        ]);
    }
}
