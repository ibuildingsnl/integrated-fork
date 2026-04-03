<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\ContentItemsBlock;
use Integrated\Bundle\BlockBundle\Document\Block\Embedded\Relation as BlockRelation;
use Integrated\Bundle\BlockBundle\Document\Block\HtmlBlock;
use Integrated\Bundle\CommentBundle\Document\Comment;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\RelationAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\BulkAction;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation as ContentRelation;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Services\ContentReverseReferenceCleaner;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ContentReverseReferenceCleanerTest extends TestCase
{
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;

    /** @var SearchContentReferenced&MockObject */
    private SearchContentReferenced $searchContentReferenced;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->searchContentReferenced = $this->createMock(SearchContentReferenced::class);
    }

    public function testCleanupRemovesKnownReverseReferencesBeforeContentDelete(): void
    {
        $target = new Article();
        $target->setId('target-1');
        $target->setTitle('Delete me');

        $contentReferrer = new Article();
        $contentReferrer->setId('article-2');
        $relation = (new ContentRelation())
            ->setRelationId('rel-1')
            ->setRelationType('related')
            ->addReference($target);
        $contentReferrer->addRelation($relation);

        $htmlBlock = new HtmlBlock();
        $htmlBlock->setId('block-1');
        $htmlBlock->setRequiredItems([$target]);
        $blockRelation = (new BlockRelation())
            ->setRelationId('rel-2')
            ->setRelationType('related');
        $blockRelation->addReference($target);
        $htmlBlock->addRelation($blockRelation);

        $contentItemsBlock = new ContentItemsBlock();
        $contentItemsBlock->setId('block-2');
        $contentItemsBlock->setItems([$target]);

        $bulkAction = new BulkAction();
        $bulkAction->addSelection($target);
        $relationAction = new RelationAction();
        $relationAction->addReference($target);
        $bulkAction->addAction($relationAction);

        $comment = new Comment();
        $comment->setContent($target);

        $this->searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($target)
            ->willReturn([
                $contentReferrer,
                $htmlBlock,
                $contentItemsBlock,
                $bulkAction,
                $comment,
            ]);

        $this->documentManager
            ->expects($this->exactly(4))
            ->method('persist')
            ->with($this->logicalOr(
                $this->identicalTo($contentReferrer),
                $this->identicalTo($htmlBlock),
                $this->identicalTo($contentItemsBlock),
                $this->identicalTo($bulkAction)
            ));

        $this->documentManager
            ->expects($this->once())
            ->method('remove')
            ->with($comment);

        $this->documentManager
            ->expects($this->once())
            ->method('flush');

        $cleaner = new ContentReverseReferenceCleaner($this->documentManager, $this->searchContentReferenced);

        self::assertSame(7, $cleaner->cleanup($target));
        self::assertCount(0, $contentReferrer->getReferencesByRelationType('related'));
        self::assertCount(0, $htmlBlock->getRequiredItems());
        self::assertCount(0, $htmlBlock->getRelations());
        self::assertCount(0, $contentItemsBlock->getItems());
        self::assertCount(0, $bulkAction->getSelection());
        self::assertCount(0, iterator_to_array($relationAction->getReferences()));
    }

    public function testCleanupSkipsFlushWhenNothingReferencesContent(): void
    {
        $target = new Article();
        $target->setId('target-1');

        $this->searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($target)
            ->willReturn([]);

        $this->documentManager
            ->expects($this->never())
            ->method('persist');

        $this->documentManager
            ->expects($this->never())
            ->method('remove');

        $this->documentManager
            ->expects($this->never())
            ->method('flush');

        $cleaner = new ContentReverseReferenceCleaner($this->documentManager, $this->searchContentReferenced);

        self::assertSame(0, $cleaner->cleanup($target));
    }

    public function testCleanupWithoutFlushCanRollbackReverseReferenceChanges(): void
    {
        $target = new Article();
        $target->setId('target-1');

        $contentReferrer = new Article();
        $contentReferrer->setId('article-2');
        $relation = (new ContentRelation())
            ->setRelationId('rel-1')
            ->setRelationType('related')
            ->addReference($target);
        $contentReferrer->addRelation($relation);

        $htmlBlock = new HtmlBlock();
        $htmlBlock->setId('block-1');
        $htmlBlock->setRequiredItems([$target]);
        $blockRelation = (new BlockRelation())
            ->setRelationId('rel-2')
            ->setRelationType('related');
        $blockRelation->addReference($target);
        $htmlBlock->addRelation($blockRelation);

        $contentItemsBlock = new ContentItemsBlock();
        $contentItemsBlock->setId('block-2');
        $contentItemsBlock->setItems([$target]);

        $bulkAction = new BulkAction();
        $bulkAction->addSelection($target);
        $relationAction = new RelationAction();
        $relationAction->addReference($target);
        $bulkAction->addAction($relationAction);

        $comment = new Comment();
        $comment->setId('comment-1');
        $comment->setContent($target);

        $this->searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($target)
            ->willReturn([
                $contentReferrer,
                $htmlBlock,
                $contentItemsBlock,
                $bulkAction,
                $comment,
            ]);

        $persisted = [];
        $this->documentManager
            ->expects($this->exactly(5))
            ->method('persist')
            ->willReturnCallback(function (object $document) use (&$persisted): void {
                $persisted[] = $document;
            });

        $this->documentManager
            ->expects($this->once())
            ->method('remove')
            ->with($comment);

        $this->documentManager
            ->expects($this->never())
            ->method('flush');

        $cleaner = new ContentReverseReferenceCleaner($this->documentManager, $this->searchContentReferenced);

        $result = $cleaner->cleanupWithoutFlush($target);

        self::assertSame(7, $result['changed']);
        self::assertCount(0, $contentReferrer->getReferencesByRelationType('related'));
        self::assertCount(0, $htmlBlock->getRequiredItems());
        self::assertCount(0, $htmlBlock->getRelations());
        self::assertCount(0, $contentItemsBlock->getItems());
        self::assertCount(0, $bulkAction->getSelection());
        self::assertCount(0, iterator_to_array($relationAction->getReferences()));

        $result['rollback']();

        self::assertCount(1, $contentReferrer->getReferencesByRelationType('related'));
        self::assertCount(1, $htmlBlock->getRequiredItems());
        self::assertCount(1, $htmlBlock->getRelations());
        self::assertCount(1, $contentItemsBlock->getItems());
        self::assertCount(1, $bulkAction->getSelection());
        self::assertCount(1, iterator_to_array($relationAction->getReferences()));
        self::assertTrue(\in_array($comment, $persisted, true));
    }

    public function testCleanupOnlyTouchesSupportedReverseReferenceDocuments(): void
    {
        $target = new Image();
        $target->setId('image-1');

        $contentReferrer = new Article();
        $contentReferrer->setId('article-2');
        $relation = (new ContentRelation())
            ->setRelationId('rel-1')
            ->setRelationType('related')
            ->addReference($target);
        $contentReferrer->addRelation($relation);

        $page = new CleanerTestPage();
        $page->setId('page-1');

        $this->searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($target)
            ->willReturn([$contentReferrer, $page]);

        $this->documentManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($contentReferrer));

        $this->documentManager
            ->expects($this->never())
            ->method('remove');

        $this->documentManager
            ->expects($this->once())
            ->method('flush');

        $cleaner = new ContentReverseReferenceCleaner($this->documentManager, $this->searchContentReferenced);

        self::assertSame(1, $cleaner->cleanup($target));
        self::assertCount(0, $contentReferrer->getReferencesByRelationType('related'));
    }
}

final class CleanerTestPage extends AbstractPage
{
    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
