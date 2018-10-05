<?php
declare(strict_types=1);

namespace DataFlow\Tests\Unit\Doctrine\Hydrator\Strategy\Decoration;

use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\DecoratedStrategy;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\Decoration\NullDecorator;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\NestedEntityStrategy;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\ObjectProperty;

class A
{
    public $a;
    public $b;
}

class B
{
    public $a;
    public $b;
}

final class NullDecoratorTest extends \Codeception\Test\Unit
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    protected function setUp()
    {
        $this->hydrator = new ObjectProperty();
        $this->hydrator->addStrategy('b', new DecoratedStrategy(
            new NestedEntityStrategy(new ObjectProperty()),
            new NullDecorator()
        ));
    }

    /**
     * @param A $a
     * @param array $expected
     *
     * @dataProvider hydratorsDataProvider
     */
    public function testExtract(A $a, array $expected): void
    {
        $this->assertSame($expected, $this->hydrator->extract($a));
    }

    public function hydratorsDataProvider(): array
    {
        $a1 = new A();
        $a1->a = 10;
        $b1 = new B();
        $b1->a = 15;
        $b1->b = 'bar';
        $a1->b = $b1;

        $a2 = new A();
        $a2->a = 14;
        $a2->b = null;

        return [
            [
                $a1,
                [
                    'a' => 10,
                    'b' => [
                        'a' => 15,
                        'b' => 'bar'
                    ],
                ],
            ],
            [
                $a2,
                [
                    'a' => 14,
                    'b' => null,
                ],
            ],
        ];
    }
}
