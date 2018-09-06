<?php
declare(strict_types=1);

namespace codecept;

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Authentication\Entities\Grant;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class InvalidateTokenCest
{
    /**
     * @var int
     */
    private $tokenId;

    public function _before(ApiTester $I)
    {
        $userId = $I->haveInRepository(User::class, [
            'first' => 'Tester2',
            'last' => 'Tester2',
            'email' => 'test2@example.com',
        ]);

        $user = $I->grabEntityFromRepository(User::class, ['id' => $userId]);

        $this->tokenId = $I->haveInRepository(Token::class, [
            'owner' => $user,
            'active' => true,
            'token' => 'yyy',
            'due' => new \DateTime('+1 year'),
            'createdAt' => new \DateTime(),
        ]);

        $token = $I->grabEntityFromRepository(Token::class, ['id' => $this->tokenId]);

        $I->haveInRepository(Grant::class, [
            'token' => $token,
            'resource' => 'invalidate_token',
        ]);

        $I->amBearerAuthenticated('yyy');
    }

    public function invalidateTokenSuccess(ApiTester $I)
    {
        $I->wantTo('invalidate my token');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('/invalidatetoken/' . (string)$this->tokenId);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'token' => [
                    'owner' => [
                        'email' => 'test2@example.com',
                    ],
                    'active' => 0,
                ],
            ],
        ]);
    }

    public function invalidateTokenNoPermissions(ApiTester $I)
    {
        $I->wantTo('invalidate someone elses token');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('/invalidatetoken/1');
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [],
        ]);
    }

    public function invalidateTokenCanNotFindToken(ApiTester $I)
    {
        $I->wantTo('invalidate token that doesn\'t exist');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('/invalidatetoken/100');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'token' => null,
            ],
        ]);
    }
}
