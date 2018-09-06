<?php
declare(strict_types=1);

namespace codecept;

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Authentication\Entities\Grant;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class InvalidateTokensCest
{
    public function _before(ApiTester $I)
    {
        $userId = $I->haveInRepository(User::class, [
            'first' => 'Tester2',
            'last' => 'Tester2',
            'email' => 'test2@example.com',
        ]);

        $user = $I->grabEntityFromRepository(User::class, ['id' => $userId]);

        $tokenId = $I->haveInRepository(Token::class, [
            'owner' => $user,
            'active' => true,
            'token' => 'yyy',
            'due' => new \DateTime('+1 year'),
            'createdAt' => new \DateTime(),
        ]);

        $token = $I->grabEntityFromRepository(Token::class, ['id' => $tokenId]);

        $I->haveInRepository(Grant::class, [
            'token' => $token,
            'resource' => 'invalidate_tokens',
        ]);

        $I->amBearerAuthenticated('yyy');
    }

    public function invalidateTokensSuccess(ApiTester $I)
    {
        $I->wantTo('invalidate other user\'s token');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/invalidatetokens', [
            'users' => [1]
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'tokens' => [
                    [
                        'owner' => [
                            'email' => 'test1@example.com',
                        ],
                        'active' => 0,
                    ]
                ],
                'count' => 1
            ],
        ]);
    }

    public function invalidateAllTokensSuccess(ApiTester $I)
    {
        $I->wantTo('invalidate all tokens');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/invalidatetokens');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'count' => 2
            ],
        ]);
    }

    public function invalidateTokesWrongUsers(ApiTester $I)
    {
        $I->wantTo('invalidate wrong user\'s token');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/invalidatetokens', [
            'users' => [10]
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'success' => false,
            'data' => [
                'tokens' => [],
                'count' => 0
            ],
        ]);
    }
}
