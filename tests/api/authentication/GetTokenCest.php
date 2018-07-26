<?php
declare(strict_types=1);

use Codeception\Module\CleanDoctrine2;
use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Authentication\Service\PasswordManager;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class GetTokenCest
{
    /**
     * @var CleanDoctrine2
     */
    private $doctrine;

    public function _inject(CleanDoctrine2 $cleanDoctrine2)
    {
        $this->doctrine = $cleanDoctrine2;
    }

    public function _before(ApiTester $I)
    {
        $userId = $I->haveInRepository(User::class, [
            'first' => 'Tester2',
            'last' => 'Tester2',
            'email' => 'test2@example.com',
        ]);

        $user = $I->grabEntityFromRepository(User::class, ['id' => $userId]);

        $logger = new \Monolog\Logger('log', [
            new \Monolog\Handler\NoopHandler()
        ]);
        $passwordManager = new PasswordManager(
            $this->doctrine->em,
            $logger
        );
        $I->haveInRepository(Password::class, [
            'owner' => $user,
            'hash' => $passwordManager->getHash('test123'),
            'createdAt' => new DateTime(),
            'due' => new DateTime('+1 year'),
            'active' => true,
        ]);

        $resources = [
            'do_something_awesome',
        ];
        foreach ($resources as $key => $resource) {
            $I->haveInRepository(Permission::class, [
                'id' => ++$key,
                'user' => $user,
                'resource' => $resource,
            ]);
        }
        // cancel current Auth header
        $I->deleteHeader('Authorization');
    }

    public function createTokenSuccess(ApiTester $I)
    {
        $I->wantTo('get token for performing operations with the app');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettoken', [
            'user' => 'test2@example.com',
            'password' => 'test123',
            'resources' => [
                'do_something_awesome',
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

    public function createTokenWrongPassword(ApiTester $I)
    {
        $I->wantTo('attempt to get token, but specify wrong password');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettoken', [
            'user' => 'test2@example.com',
            'password' => 'abracadabra111',
            'resources' => [
                'do_something_awesome',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'token' => null,
            ],
        ]);
    }

    public function createTokenWrongNoPermissions(ApiTester $I)
    {
        $I->wantTo('attempt to get token for resource that is not permitted');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettoken', [
            'user' => 'test2@example.com',
            'password' => 'test123',
            'resources' => [
                'do_something_less_awesome',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'token' => null,
            ],
        ]);
    }

    public function createTokenValidationError(ApiTester $I)
    {
        $I->wantTo('attempt to get token with wrong parameters');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettoken', [
            'user' => 'test2@example.com',
            'resources' => [
                'do_something_awesome',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'token' => null,
                'validation' => [
                    [
                        'field' => 'password',
                    ],
                ]
            ],
        ]);
    }
}
