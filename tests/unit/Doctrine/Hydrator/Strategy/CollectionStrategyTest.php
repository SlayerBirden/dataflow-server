<?php
declare(strict_types=1);

namespace DataFlow\Tests\Unit\Doctrine\Hydrator\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use SlayerBirden\DataFlowServer\Doctrine\Hydrator\Strategy\CollectionStrategy;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\ObjectProperty;

class A
{
    public $a = 'bar';
    public $bars;
}

class B
{
    public $b = 'baz';
    public $baz = 'b';
}

final class CollectionStrategyTest extends \Codeception\Test\Unit
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    protected function setUp()
    {
        $this->hydrator = new ObjectProperty();
        $this->hydrator->addStrategy('bars', new CollectionStrategy(new ObjectProperty(), B::class));
    }

    public function testExtract(): void
    {
        $b2 = new B();
        $b2->baz = 'c';
        $b2->b = 'c';
        $a = new A();
        $a->bars = new ArrayCollection([new B(), $b2]);

        $expected = [
            'a' => 'bar',
            'bars' => [
                [
                    'baz' => 'b',
                    'b' => 'baz',
                ],
                [
                    'baz' => 'c',
                    'b' => 'c',
                ],
            ],
        ];

        $this->assertEquals($expected, $this->hydrator->extract($a));
    }

    public function testHydrate(): void
    {
        $b1 = new B();
        $b2 = new B();
        $b2->baz = 'c';
        $a = new A();
        $a->bars = new ArrayCollection([$b1, $b2]);

        $data = [
            'a' => 'bar',
            'bars' => [
                [
                    'baz' => 'b',
                    'b' => 'baz',
                ],
                [
                    'baz' => 'c',
                    'b' => 'baz',
                ],
            ],
        ];

        $this->assertEquals($a, $this->hydrator->hydrate($data, new A()));
    }

    /**
     * @expectedException \Zend\Hydrator\Exception\InvalidArgumentException
     * @expectedExceptionMessage Value needs to be a Doctrine Collection, got array instead
     */
    public function testExtractWrongType(): void
    {
        $b2 = new B();
        $b2->baz = 'c';
        $b2->b = 'c';
        $a = new A();
        $a->bars = [new B(), $b2];

        $this->hydrator->extract($a);
    }

    /**
     * @expectedException \Zend\Hydrator\Exception\InvalidArgumentException
     * @expectedExceptionMessage Value needs to be an instance of
     */
    public function testExtractWrongChildrenType(): void
    {
        $a = new A();
        $a->bars = new ArrayCollection([new B(), new \stdClass()]);

        $this->hydrator->extract($a);
    }

    /**
     * @expectedException \Zend\Hydrator\Exception\InvalidArgumentException
     * @expectedExceptionMessage Value needs to be an Iterable, got stdClass instead.
     */
    public function testHydrateWrongType(): void
    {
        $data = [
            'a' => 'bar',
            'bars' => new \stdClass(),
        ];

        $this->hydrator->hydrate($data, new A());
    }

    /**
     * @expectedException \Zend\Hydrator\Exception\InvalidArgumentException
     * @expectedExceptionMessage Object class name does not exist: "baz"
     */
    public function testDuringConstruct(): void
    {
        new CollectionStrategy(new ObjectProperty(), 'baz');
    }
}
