<?php

/**
 * This file is generated by SlayerBirden\DFCodeGeneration
 */

declare(strict_types=1);

namespace codecept\pipeline\type;

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Pipeline\Entities\Type;
use codecept\ApiTester;

class DeleteCest
{
    public function _before(ApiTester $I): void
    {
        $I->haveInRepository(Type::class, [
            'code' => 'dolore',
            'tablename' => 'perferendis',
        ]);
    }

    public function deleteType(ApiTester $I): void
    {
        $I->wantTo('delete type with code dolore');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE('/type/dolore');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'data' => [
                'type' => [
                    'code' => 'dolore',
                    'tablename' => 'perferendis',
                ]
            ]
        ]);
    }

    public function deleteNonExistingType(ApiTester $I): void
    {
        $I->wantTo('delete non-existing type');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE('/type/bar');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'data' => [
                'type' => null
            ]
        ]);
    }
}
