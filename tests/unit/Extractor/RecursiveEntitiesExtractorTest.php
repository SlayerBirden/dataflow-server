<?php

use SlayerBirden\DataFlowServer\Extractor\RecursiveEntitiesExtractor;

class RecursiveEntitiesExtractorTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testExtractValue()
    {
        include 'Entities/Dummy.php';

        $childUpdated = new DateTime('2018-03-06');
        $parentUpdated = new DateTime('2019-01-01');

        $child = new Dummy();
        $child->setId(1);
        $child->setTitle('child dummy');
        $child->setUpdatedAt($childUpdated);

        $parent = new Dummy();
        $parent->setId(2);
        $parent->setTitle('parent dummy');
        $parent->setUpdatedAt($parentUpdated);
        $parent->setChild($child);

        $extractor = new RecursiveEntitiesExtractor();
        $expected = [
            'id' => 2,
            'title' => 'parent dummy',
            'child' => [
                'id' => 1,
                'title' => 'child dummy',
                'updated_at' => $childUpdated->format(\DateTime::RFC3339),
                'child' => null,
            ],
            'updated_at' => $parentUpdated->format(\DateTime::RFC3339),
        ];

        $this->assertEquals($expected, $extractor->extract($parent));
    }
}
