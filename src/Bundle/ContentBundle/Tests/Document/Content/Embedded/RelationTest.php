<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Document\Content\Embedded;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class RelationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Relation
     */
    private $relation;

    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        $this->relation = new Relation();
    }

    /**
     * Test get- and setReferences functions.
     */
    public function testGetAndSetReferencesFunction()
    {
        $references = [
            $this->createMock('\Integrated\Common\Content\ContentInterface'),
            $this->createMock('\Integrated\Common\Content\ContentInterface'),
        ];

        $this->assertSame($references, $this->relation->setReferences($references)->getReferences());
    }

    /**
     * Test addReferences function.
     */
    public function testAddReferencesFunction()
    {
        // Create references and add them
        $references = [
            $this->createMock('\Integrated\Common\Content\ContentInterface'),
            $this->createMock('\Integrated\Common\Content\ContentInterface'),
        ];

        $this->relation->addReferences($references);

        $references[] = $content = $this->createMock('\Integrated\Common\Content\ContentInterface');

        $this->relation->addReferences([$content]);

        // Asserts
        $this->assertSame($references, $this->relation->getReferences());
    }

    /**
     * Test addReference functions.
     */
    public function testAddReferenceFunction()
    {
        /* @var $content \Integrated\Common\Content\ContentInterface | MockObject */
        $content = $this->createMock('\Integrated\Common\Content\ContentInterface');

        $this->relation->addReference($content);

        // Asserts
        $this->assertSame([$content], $this->relation->getReferences());
    }

    /**
     * Test addReference function with duplicate reference.
     */
    public function testAddReferenceFunctionWithDuplicateReference()
    {
        /* @var $content \Integrated\Common\Content\ContentInterface | MockObject */
        $content = $this->createMock('\Integrated\Common\Content\ContentInterface');

        // Add content two times
        $this->relation->addReference($content)->addReference($content);

        // Asserts
        $this->assertCount(1, $this->relation->getReferences());
    }

    /**
     * Test removeReference function.
     */
    public function testRemoveReferenceFunction()
    {
        /* @var $content \Integrated\Common\Content\ContentInterface | MockObject */
        $content = $this->createMock('\Integrated\Common\Content\ContentInterface');

        // Add content
        $this->relation->addReference($content);

        // Asserts
        $this->assertTrue($this->relation->removeReference($content));
    }

    /**
     * Test removeReference function with invalid content.
     */
    public function testRemoveReferenceFunctionWithInvalidContent()
    {
        /* @var $content \Integrated\Common\Content\ContentInterface | MockObject */
        $content = $this->createMock('\Integrated\Common\Content\ContentInterface');

        // Asserts
        $this->assertFalse($this->relation->removeReference($content));
    }
}
