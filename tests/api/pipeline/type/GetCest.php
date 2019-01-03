<?php

/**
 * This file is generated by SlayerBirden\DFCodeGeneration
 */

declare(strict_types=1);

namespace codecept\pipeline\type;

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Pipeline\Entities\Type;
use codecept\ApiTester;

class GetCest
{
    public function _before(ApiTester $I): void
    {
        $I->haveInRepository(Type::class, [
            'code' => 'quaerat',
            'tablename' => 'Reprehenderit et maiores blanditiis itaque.',
        ]);
    }

    public function getType(ApiTester $I): void
    {
        $I->wantTo('get type with code quaerat');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/type/quaerat');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'data' => [
                'type' => [
                    'code' => 'quaerat',
                    'tablename' => 'Reprehenderit et maiores blanditiis itaque.',
                ]
            ]
        ]);
    }

    public function getNonExistingType(ApiTester $I): void
    {
        $I->wantTo('get non-existing type');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/type/baz');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'data' => [
                'type' => null
            ]
        ]);
    }

    public function getWithInvalidIdType(ApiTester $I): void
    {
        $I->wantTo('get type using invalid id');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/type/0');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
