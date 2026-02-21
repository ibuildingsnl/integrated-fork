<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Document\Relation;

use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Common\Content\Relation\RelationInterface;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class RelationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Relation must implement RelationInterface.
     */
    public function testInterface()
    {
        $this->assertInstanceOf(RelationInterface::class, $this->getInstance());
    }

    /**
     * Test if all the default values are set.
     */
    public function testDefaultValues()
    {
        $instance = $this->getInstance();

        $this->assertSame([], $instance->getTargets());
        $this->assertSame([], $instance->getSources());

        $this->assertFalse($instance->isMultiple());
        $this->assertFalse($instance->isRequired());

        $this->assertInstanceOf(\DateTime::class, $instance->getCreatedAt());
    }

    /**
     * Test the get- and setId function.
     */
    public function testGetAndSetIdFunction()
    {
        $instance = $this->getInstance();
        $instance->setId($id = 'id');

        $this->assertEquals($id, $instance->getId());
    }

    /**
     * Test get- and setName function.
     */
    public function testGetAndSetNameFunction()
    {
        $instance = $this->getInstance();
        $instance->setName($name = 'name');

        $this->assertEquals($name, $instance->getName());
    }

    /**
     * Test get- and setType function.
     */
    public function testGetAndSetTypeFunction()
    {
        $instance = $this->getInstance();
        $instance->setType($type = 'type');

        $this->assertEquals($type, $instance->getType());
    }

    /**
     * Test get- and setSources function with valid collection.
     */
    #[DataProvider('validCollectionProvider')]
    public function testGetAndSetSourcesFunctionWithValidCollection(array $collection)
    {
        $instance = $this->getInstance();
        $instance->setSources($collection);

        $this->assertEquals($collection, $instance->getSources());
    }

    /**
     * Test get- and setSources function with invalid collection.
     */
    #[DataProvider('invalidCollectionProvider')]
    public function testGetAndSetSourcesFunctionWithInvalidCollection(array $collection)
    {
        $this->expectException(\TypeError::class);

        $instance = $this->getInstance();
        $instance->setSources($collection);
    }

    /**
     * Test addSource function with duplicate source.
     */
    public function testAddSourceFunctionWithDuplicateSource()
    {
        $instance = $this->getInstance();

        $instance->addSource($source = new ContentType());
        $instance->addSource($source);

        $this->assertSame([$source], $instance->getSources());
    }

    /**
     * Test removeSource function with existing source.
     */
    public function testRemoveSourceFunctionWithExistingSource()
    {
        $instance = $this->getInstance();

        $instance->addSource($source = new ContentType());
        $instance->removeSource($source);

        $this->assertEmpty($instance->getSources());
    }

    /**
     * Test removeSource function with non existing source.
     */
    public function testRemoveSourceFunctionWithNonExistingSource()
    {
        $instance = $this->getInstance();

        $instance->addSource($source = new ContentType());
        $instance->removeSource(new ContentType());

        $this->assertSame([$source], $instance->getSources());
    }

    /**
     * Test get- and setTargets function with valid collection.
     */
    #[DataProvider('validCollectionProvider')]
    public function testGetAndSetTargetsFunctionWithValidCollection(array $collection)
    {
        $instance = $this->getInstance();
        $instance->setTargets($collection);

        $this->assertEquals($collection, $instance->getTargets());
    }

    /**
     * Test get- and setTargets function with invalid collection.
     */
    #[DataProvider('invalidCollectionProvider')]
    public function testGetAndSetTargetsFunctionWithInvalidCollection(array $collection)
    {
        $this->expectException(\TypeError::class);

        $instance = $this->getInstance();
        $instance->setTargets($collection);
    }

    /**
     * Test addTarget function with duplicate target.
     */
    public function testAddTargetFunctionWithDuplicateTarget()
    {
        $instance = $this->getInstance();

        $instance->addTarget($target = new ContentType());
        $instance->addTarget($target);

        $this->assertSame([$target], $instance->getTargets());

        $instance = $this->getInstance();
    }

    /**
     * Test removeTarget function with existing target.
     */
    public function testRemoveTargetFunctionWithExistingTarget()
    {
        $instance = $this->getInstance();

        $instance->addTarget($target = new ContentType());
        $instance->removeTarget($target);

        $this->assertEmpty($instance->getTargets());
    }

    /**
     * Test removeTarget function with non existing source.
     */
    public function testRemoveTargetFunctionWithNonExistingSource()
    {
        $instance = $this->getInstance();

        $instance->addTarget($target = new ContentType());
        $instance->removeTarget(new ContentType());

        $this->assertSame([$target], $instance->getTargets());
    }

    /**
     * Test is- and setMultiple function.
     */
    public function testIsAndSetMultipleFunction()
    {
        $instance = $this->getInstance();
        $instance->setMultiple(true);

        $this->assertTrue($instance->isMultiple());

        $instance->setMultiple(false);

        $this->assertFalse($instance->isMultiple());
    }

    /**
     * Test is- and setRequired function.
     */
    public function testIsAndSetRequiredFunction()
    {
        $instance = $this->getInstance();
        $instance->setRequired(true);

        $this->assertTrue($instance->isRequired());

        $instance->setRequired(false);

        $this->assertFalse($instance->isRequired());
    }

    /**
     * Test get- and setCreatedAt function.
     */
    public function testGetAndSetCreatedAtFunction()
    {
        $instance = $this->getInstance();
        $instance->setCreatedAt($time = new \DateTime());

        $this->assertEquals($time, $instance->getCreatedAt());
    }

    public static function validCollectionProvider(): array
    {
        return [
            [
                [new ContentType(), new ContentType()],
            ],
            [
                [new ContentType()],
            ],
        ];
    }

    public static function invalidCollectionProvider(): array
    {
        return [
            [
                ['Invalid', true, ['types']],
            ],
        ];
    }

    /**
     * @return Relation
     */
    protected function getInstance()
    {
        return new Relation();
    }
}
