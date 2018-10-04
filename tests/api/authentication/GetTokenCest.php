<?php
declare(strict_types=1);

namespace codecept\authentication;

use codecept\ApiTester;
use codecept\Helper\CleanDoctrine2;
use codecept\Helper\ZendExpressive3;
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
    /**
     * @var ZendExpressive3
     */
    private $expressive;

    public function _inject(CleanDoctrine2 $cleanDoctrine2, ZendExpressive3 $expressive)
    {
        $this->doctrine = $cleanDoctrine2;
        $this->expressive = $expressive;
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
            $this->expressive->container->get('PasswordRepository'),
            $logger
        );
        $I->haveInRepository(Password::class, [
            'owner' => $user,
            'hash' => $passwordManager->getHash('test123'),
            'createdAt' => new \DateTime(),
            'due' => new \DateTime('+1 year'),
            'active' => true,
        ]);

        $resources = [
            'create_password',
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
                'create_password',
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
                'create_password',
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
                'get_tmp_token',
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
                'create_password',
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

    public function createTokenEmptyResources(ApiTester $I)
    {
        $I->wantTo('attempt to get token with empty resources');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/gettoken', [
            'user' => 'test2@example.com',
            'password' => 'test123',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['validation' => [
            [
                'field' => 'resources',
            ],
        ]]);
    }
}
