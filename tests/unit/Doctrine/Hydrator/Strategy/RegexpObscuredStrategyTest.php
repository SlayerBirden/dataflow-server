<?php
declare(strict_types=1);

namespace DataFlow\Tests\Unit\Doctrine\Hydrator\Strategy;

use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\RegexpObscuredStrategy;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\ObjectProperty;

class Unit
{
    public $name;
    public $code;
}

final class RegexpObscuredStrategyTest extends \Codeception\Test\Unit
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    protected function setUp()
    {
        $this->hydrator = new ObjectProperty();
        $this->hydrator->addStrategy('code', new RegexpObscuredStrategy('/[abc]{2}/', '**'));
    }

    public function testExtract(): void
    {
        $obj = new Unit();
        $obj->name = 'Alfred';
        $obj->code = 'abruptly';

        $this->assertSame([
            'name' => 'Alfred',
            'code' => '**ruptly',
        ], $this->hydrator->extract($obj));
    }

    public function testHydrate(): void
    {
        $obj = new Unit();
        $obj->name = 'Alfred';
        $obj->code = 'aa';
        $this->assertEquals($obj, $this->hydrator->hydrate([
            'name' => 'Alfred',
            'code' => 'aa',
        ], new Unit()));
    }
}
