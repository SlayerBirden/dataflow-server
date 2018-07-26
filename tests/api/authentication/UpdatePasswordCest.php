<?php
declare(strict_types=1);

use Codeception\Module\CleanDoctrine2;
use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Authentication\Entities\Grant;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Authentication\Service\PasswordManager;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class UpdatePasswordCest
{
    /**
     * @var CleanDoctrine2
     */
    private $doctrine;
    /**
     * @var int
     */
    private $userId;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function _inject(CleanDoctrine2 $cleanDoctrine2)
    {
        $this->doctrine = $cleanDoctrine2;
        $this->logger = new \Monolog\Logger('log', [
            new \Monolog\Handler\NoopHandler()
        ]);
    }

    public function _before(ApiTester $I)
    {
        $this->userId = $I->haveInRepository(User::class, [
            'first' => 'Tester2',
            'last' => 'Tester2',
            'email' => 'test2@example.com',
        ]);

        $user = $I->grabEntityFromRepository(User::class, ['id' => $this->userId]);
        $passwordManager = new PasswordManager(
            $this->doctrine->em,
            $this->logger
        );

        $I->haveInRepository(Password::class, [
            'owner' => $user,
            'hash' => $passwordManager->getHash('test123'),
            'createdAt' => new DateTime(),
            'due' => new DateTime('+1 year'),
            'active' => true,
        ]);

        $tokenId = $I->haveInRepository(Token::class, [
            'owner' => $user,
            'active' => true,
            'token' => 'yyy',
            'due' => new DateTime('+1 year'),
            'createdAt' => new DateTime(),
        ]);

        $token = $I->grabEntityFromRepository(Token::class, ['id' => $tokenId]);

        $I->haveInRepository(Grant::class, [
            'token' => $token,
            'resource' => 'update_password',
        ]);

        $I->amBearerAuthenticated('yyy');
    }

    public function updatePasswordSuccess(ApiTester $I)
    {
        $I->wantTo('update my password');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/updatepassword', [
            'password' => 'test123',
            'new_password' => 'there is a clown on a wing',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
        ]);

        // check that new password works
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['id' => $this->userId]);
        $passwordManager = new PasswordManager(
            $this->doctrine->em,
            $this->logger
        );
        $valid = $passwordManager->isValidForUser('there is a clown on a wing', $user);

        $I->assertSame(true, $valid);
    }

    public function updatePasswordValidationError(ApiTester $I)
    {
        $I->wantTo('update my password but providing invalid data');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/updatepassword', [
            'password' => 'test123',
            'new_password' => 'short',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'validation' => [
                    [
                        'field' => 'new_password',
                    ]
                ]
            ]
        ]);
    }

    public function updatePasswordOldPasswordWrong(ApiTester $I)
    {
        $I->wantTo('update my password but providing wrong old pw');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/updatepassword', [
            'password' => 'forgot',
            'new_password' => 'cool new password',
        ]);
        $I->seeResponseCodeIs(HttpCode::PRECONDITION_FAILED);
        $I->seeResponseContainsJson([
            'success' => false,
        ]);
    }

    public function updatePasswordWithoutProvidingOld(ApiTester $I)
    {
        $I->wantTo('update my password without providing old one');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/updatepassword', [
            'new_password' => 'cool new password',
        ]);
        $I->seeResponseCodeIs(HttpCode::PRECONDITION_FAILED);
        $I->seeResponseContainsJson([
            'success' => false,
        ]);
    }

    public function updatePasswordUseUsedPw(ApiTester $I)
    {
        $I->wantTo('update my password using old password which is already in the system');

        $user = $I->grabEntityFromRepository(User::class, ['id' => $this->userId]);
        $passwordManager = new PasswordManager(
            $this->doctrine->em,
            $this->logger
        );
        $I->haveInRepository(Password::class, [
            'owner' => $user,
            'hash' => $passwordManager->getHash('old cool long password'),
            'createdAt' => new DateTime('-1 year'),
            'due' => new DateTime('-1 month'),
            'active' => false,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/updatepassword', [
            'password' => 'test123',
            'new_password' => 'old cool long password',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
        ]);
    }
}
