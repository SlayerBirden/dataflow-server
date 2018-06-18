<?php
declare(strict_types=1);

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class CreatePasswordCest
{
    public function _before(ApiTester $I)
    {
        $I->haveInRepository(User::class, [
            'id' => 2,
            'first' => 'Tester2',
            'last' => 'Tester2',
            'email' => 'test2@example.com',
        ]);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function createPasswordSuccess(ApiTester $I)
    {
        $I->wantTo('create password');
        $this->authForUser($I, 2);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/password', [
            'password' => 'test123',
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
        $this->authForUser($I, 2);
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

    public function createPasswordUserAlreadyHasPassword(ApiTester $I)
    {
        $I->wantTo('create password for user already having one');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/password', [
            'password' => 'test123',
        ]);
        $I->seeResponseCodeIs(HttpCode::PRECONDITION_FAILED);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'password' => null,
            ],
        ]);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     */
    public function createPasswordException(ApiTester $I)
    {
        $I->wantTo('create password while not providing one');
        $this->authForUser($I, 2);
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
                'validation' => []
            ],
        ]);
    }
}
