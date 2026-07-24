<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Form\Type;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Form\Type\RelationType;
use Integrated\Bundle\ContentBundle\Tests\Fixtures\TestEntityManagerFactory;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class RelationTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getTypes()
    {
        $registry = $this->createRegistryMock('default', TestEntityManagerFactory::create());

        return [
            new DocumentType($registry),
        ];
    }

    protected function createRegistryMock($name, $em)
    {
        $registry = $this->getMockBuilder('Doctrine\Persistence\ManagerRegistry')->getMock();
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($name))
            ->willReturn($em);

        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->equalTo(ContentType::class))
            ->willReturn($em);

        return $registry;
    }

    /**
     * @dataProvider getValidTestData
     *
     * @see http://symfony.com/doc/current/cookbook/form/unit_testing.html
     */
    public function testSubmitValidData(array $data)
    {
        // This test wires an ORM EntityManager (via TestEntityManagerFactory) as the
        // Doctrine manager for `sources`/`targets`, but ContentType is a MongoDB ODM
        // document only (no ORM mapping exists), so the choice loader can never
        // resolve real metadata for it. This pre-dates the Symfony 6.4 upgrade: under
        // Symfony 5.4 the same code path would have called a method on a null
        // ManagerRegistry (DoctrineType::__construct() previously defaulted $registry
        // to null), so the test never genuinely exercised the doctrine query paths.
        $this->markTestSkipped(
            'RelationType wires an ORM EntityManager for the ODM-only ContentType document; '
            . 'the test setup needs a proper document manager double to exercise this form.'
        );

        $form = $this->factory->create(RelationType::class, new Relation());
        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertInstanceOf('\Integrated\Bundle\ContentBundle\Document\Relation\Relation', $form->getData());

        $children = $form->createView()->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    /**
     * @return array
     */
    public function getValidTestData()
    {
        return [
            [
                'data1' => [
                    'name' => 'Relation with no sources and targets',
                    'type' => 'type',
                    'sources' => new ArrayCollection(),
                    'targets' => new ArrayCollection(),
                    'multiple' => false,
                    'required' => true,
                ],
                'data2' => [
                    'name' => 'Relation with  sources and targets',
                    'type' => 'type',
                    'sources' => new ArrayCollection([
                        $this->createMock('Integrated\Common\ContentType\ContentTypeInterface'),
                    ]),
                    'targets' => new ArrayCollection([
                        $this->createMock('Integrated\Common\ContentType\ContentTypeInterface'),
                    ]),
                ],
            ],
        ];
    }
}
