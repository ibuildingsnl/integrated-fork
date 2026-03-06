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

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Bulk\RelationAddHandler;
use Integrated\Bundle\ContentBundle\Bulk\RelationRemoveHandler;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionRelationReferencesType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionRelationType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BulkActionRelationTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $generator = $this->createMock(UrlGeneratorInterface::class);
        $generator->method('generate')->willReturn('/admin/content/json');

        $documentManager = $this->createMock(DocumentManager::class);

        return [
            new PreloadedExtension([
                new BulkActionRelationType($generator, 'integrated_content_content_index'),
                new BulkActionRelationReferencesType($documentManager),
            ], []),
        ];
    }

    public function testTaxonomyCategoryBuildsChoicesFromTaxonomyOverview(): void
    {
        $relation = (new Relation())
            ->setId('dossier')
            ->setName('Dossier')
            ->setType('taxonomy_category');

        $categoryWithGetter = new class {
            public function getTaxonomyId(): string
            {
                return 'cat-2';
            }
        };

        $form = $this->factory->create(BulkActionRelationType::class, null, [
            'label' => 'Add Dossier',
            'relation' => $relation,
            'relation_handler' => 'handler',
            'taxonomy_categories' => [
                ['taxonomyId' => 'cat-1'],
                $categoryWithGetter,
                ['title' => 'missing-taxonomy-id'],
            ],
        ]);

        self::assertSame(
            ['cat-1' => 'cat-1', 'cat-2' => 'cat-2'],
            $form->get('references')->getConfig()->getOption('choices')
        );
    }

    public function testTaxonomyAddActionContainsReplaceExistingSwitcher(): void
    {
        $relation = (new Relation())
            ->setId('dossier')
            ->setName('Dossier')
            ->setType('taxonomy_category');

        $form = $this->factory->create(BulkActionRelationType::class, null, [
            'label' => 'Add Dossier',
            'relation' => $relation,
            'relation_handler' => RelationAddHandler::class,
            'taxonomy_categories' => [],
        ]);

        $form->submit([
            'references' => [],
            'replaceExisting' => true,
        ]);

        self::assertTrue($form->has('replaceExisting'));
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->getData()->isReplaceExisting());
    }

    public function testTagAddActionContainsReplaceExistingSwitcher(): void
    {
        $relation = (new Relation())
            ->setId('tag')
            ->setName('Tag')
            ->setType('taxonomy_tags');

        $form = $this->factory->create(BulkActionRelationType::class, null, [
            'label' => 'Add Tag',
            'relation' => $relation,
            'relation_handler' => RelationAddHandler::class,
            'taxonomy_categories' => [],
        ]);

        $form->submit([
            'references' => [],
            'replaceExisting' => true,
        ]);

        self::assertTrue($form->has('replaceExisting'));
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->getData()->isReplaceExisting());
    }

    public function testTaxonomyRemoveActionDoesNotContainReplaceExistingSwitcher(): void
    {
        $relation = (new Relation())
            ->setId('dossier')
            ->setName('Dossier')
            ->setType('taxonomy_category');

        $form = $this->factory->create(BulkActionRelationType::class, null, [
            'label' => 'Remove Dossier',
            'relation' => $relation,
            'relation_handler' => RelationRemoveHandler::class,
            'taxonomy_categories' => [],
        ]);

        self::assertFalse($form->has('replaceExisting'));
    }
}
