<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Bulk;

use Integrated\Bundle\ContentBundle\Bulk\RelationAddHandler;
use Integrated\Bundle\ContentBundle\Bulk\RelationHandlerFactory;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation as EmbeddedRelation;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use PHPUnit\Framework\TestCase;

class RelationHandlersTest extends TestCase
{
    public function testRelationAddHandlerCanReplaceExistingReferences(): void
    {
        $relation = $this->createRelation('dossier', 'article');
        $content = $this->createContent('article', 'content-1');
        $oldReference = $this->createContent('dossier', 'old-reference');
        $newReference = $this->createContent('dossier', 'new-reference');

        $embedded = (new EmbeddedRelation())
            ->setRelationId('dossier')
            ->setRelationType('taxonomy_category')
            ->addReference($oldReference);
        $content->addRelation($embedded);

        $handler = new RelationAddHandler($relation, [$newReference], true);
        $handler->execute($content);

        $updated = $content->getRelation('dossier');
        self::assertInstanceOf(EmbeddedRelation::class, $updated);
        self::assertCount(1, $updated->getReferences());
        self::assertSame('new-reference', $updated->getReferences()[0]->getId());
    }

    public function testRelationAddHandlerCanClearAllReferencesWhenReplaceIsEnabledAndNoSelectionIsGiven(): void
    {
        $relation = $this->createRelation('dossier', 'article');
        $content = $this->createContent('article', 'content-1');
        $oldReference = $this->createContent('dossier', 'old-reference');

        $embedded = (new EmbeddedRelation())
            ->setRelationId('dossier')
            ->setRelationType('taxonomy_category')
            ->addReference($oldReference);
        $content->addRelation($embedded);

        $handler = new RelationAddHandler($relation, [], true);
        $handler->execute($content);

        self::assertFalse($content->getRelation('dossier'));
    }

    public function testRelationFactoryDoesNotReturnNoopWhenReplaceExistingIsEnabledWithoutNewReferences(): void
    {
        $factory = new RelationHandlerFactory(RelationAddHandler::class);
        $handler = $factory->createHandler([
            'relation' => $this->createRelation('dossier', 'article'),
            'references' => [],
            'replaceExisting' => true,
        ]);

        self::assertInstanceOf(RelationAddHandler::class, $handler);
    }

    private function createRelation(string $relationId, string $sourceContentType): Relation
    {
        $source = (new ContentType())
            ->setId($sourceContentType)
            ->setName($sourceContentType)
            ->setClass(ContentStub::class);

        return (new Relation())
            ->setId($relationId)
            ->setName('Relation '.$relationId)
            ->setType('taxonomy_category')
            ->setSources([$source]);
    }

    private function createContent(string $contentType, string $id): Content
    {
        $content = new ContentStub();
        $content->setContentType($contentType);
        $content->setId($id);

        return $content;
    }
}

class ContentStub extends Content
{
    public function __toString(): string
    {
        return $this->getId() ?? '';
    }
}
