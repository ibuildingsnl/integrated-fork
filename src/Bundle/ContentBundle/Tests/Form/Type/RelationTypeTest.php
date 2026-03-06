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

use Doctrine\Bundle\MongoDBBundle\Form\DoctrineMongoDBExtension;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ManagerRegistry;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType;
use Integrated\Bundle\ContentBundle\Form\Type\RelationType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class RelationTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']);
        $metadata->method('getTypeOfField')->willReturn('string');
        $metadata->method('hasAssociation')->willReturn(false);
        $metadata->method('getIdentifierValues')->willReturn(['id' => 'test-id']);

        $queryBuilder = $this->createMock(Builder::class);

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getClassMetadata')
            ->willReturn($metadata);
        $documentManager
            ->method('getRepository')
            ->with(ContentType::class)
            ->willReturn($repository);
        $documentManager
            ->method('contains')
            ->willReturn(true);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->method('getManagerForClass')
            ->willReturn($documentManager);

        return [
            new DoctrineMongoDBExtension($registry),
            new PreloadedExtension(
                [
                    new RelationType(),
                    new CheckboxSwitcherType(),
                ],
                []
            ),
        ];
    }

    #[DataProvider('getValidTestData')]
    public function testSubmitValidData(array $data)
    {
        $form = $this->factory->create(RelationType::class, new Relation());
        $form->submit($data, false);

        $this->assertTrue($form->isSubmitted());
        $this->assertInstanceOf(Relation::class, $form->getData());

        foreach (array_keys($data) as $key) {
            if (\in_array($key, ['multiple', 'required'], true)) {
                $this->assertTrue($form->get('options')->has($key));
                continue;
            }

            $this->assertTrue($form->has($key));
        }
    }

    public static function getValidTestData(): array
    {
        return [
            [[
                'name' => 'Relation with no sources and targets',
                'type' => 'embedded',
                'sources' => [],
                'targets' => [],
                'multiple' => false,
                'required' => true,
            ]],
            [[
                'name' => 'Relation with options enabled',
                'type' => 'embedded',
                'sources' => [],
                'targets' => [],
                'multiple' => true,
                'required' => false,
            ]],
        ];
    }
}
