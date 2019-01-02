<?php

/**
 * This file is generated by SlayerBirden\DFCodeGeneration
 */

declare(strict_types=1);

namespace codecept\pipeline\pipe;

use Codeception\Util\HttpCode;
use SlayerBirden\DataFlowServer\Pipeline\Entities\Pipe;
use codecept\ApiTester;
use SlayerBirden\DataFlowServer\Pipeline\Entities\Type;

class AddCest
{

    public function _before(ApiTester $I)
    {
        $I->haveInRepository(Type::class, [
            'code' => 'test_1',
            'tablename' => 'test_1',
        ]);
    }

    public function addPipe(ApiTester $I): void
    {
        $I->wantTo('successfully create pipe');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/pipe', [
            'name' => 'adipisci',
            'type' => 'test_1',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'data' => [
                'pipe' => [
                    'name' => 'adipisci',
                ],
            ],
        ]);
    }

    public function addIncompletePipe(ApiTester $I): void
    {
        $I->wantTo('attempt to create incomplete pipe');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/pipe', [
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function addInvalidPipe(ApiTester $I): void
    {
        $I->wantTo('attempt to create invalid pipe');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/pipe', [
            'name' => 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function mutateExistingPipe(ApiTester $I): void
    {
        $I->wantTo('attempt to mutate existing record by providing ID');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/pipe', [
            'name' => 'adipisci',
            'type' => 'test_1',
        ]);

        $entities = $I->grabEntitiesFromRepository(Pipe::class);
        $lastId = (end($entities))->getId();

        $I->sendPOST('/pipe', [
            'id' => $lastId,
            'name' => 'adipisci 2nd',
            'type' => 'test_1',
        ]);

        $entities = $I->grabEntitiesFromRepository(Pipe::class);
        $newLastId = (end($entities))->getId();

        $I->assertNotEquals($lastId, $newLastId);
    }
}
