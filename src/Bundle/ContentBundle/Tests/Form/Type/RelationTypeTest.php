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

use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Form\Type\RelationType;
use Integrated\Bundle\ContentBundle\Tests\Fixtures\TestEntityManagerFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Form\Test\TypeTestCase;

class RelationTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestEntityManagerFactory::create();
    }

    #[DataProvider('getValidTestData')]
    public function testSubmitValidData(array $data)
    {
        $form = $this->factory->create(RelationType::class, new Relation());
        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertInstanceOf(Relation::class, $form->getData());

        $children = $form->createView()->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public static function getValidTestData(): array
    {
        return [
            [
                'data1' => [
                    'name' => 'Relation with no sources and targets',
                    'type' => 'type',
                    'sources' => [],
                    'targets' => [],
                    'multiple' => false,
                    'required' => true,
                ],
                'data2' => [
                    'name' => 'Relation with  sources and targets',
                    'type' => 'type',
                    'sources' => [
                        new ContentType(),
                    ],
                    'targets' => [
                        new ContentType(),
                    ],
                ],
            ],
        ];
    }
}
