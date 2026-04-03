<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Doctrine\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Integrated\Bundle\ContentBundle\Doctrine\EventListener\CheckReferencedListener;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class CheckReferencedListenerTest extends TestCase
{
    /** @var SearchContentReferenced&MockObject */
    private SearchContentReferenced $searchContentReferenced;

    protected function setUp(): void
    {
        $this->searchContentReferenced = $this->createMock(SearchContentReferenced::class);
    }

    public function testPreRemoveAllowsUnreferencedContent(): void
    {
        $document = new Article();
        $document->setId('article-1');
        $document->setTitle('Unreferenced article');

        $this->searchContentReferenced
            ->expects($this->once())
            ->method('getReferenced')
            ->with($document)
            ->willReturn([]);

        $listener = new CheckReferencedListener($this->searchContentReferenced);

        $listener->preRemove($this->createLifecycleEvent($document));

        $this->addToAssertionCount(1);
    }

    public function testPreRemoveThrowsHelpfulMessageForReferencedContent(): void
    {
        $document = new Article();
        $document->setId('article-42');
        $document->setTitle('Gelato update');

        $this->searchContentReferenced
            ->expects($this->once())
            ->method('getReferenced')
            ->with($document)
            ->willReturn([
                'page-1' => ['id' => 'page-1', 'name' => 'Homepage teaser'],
                'page-2' => ['id' => 'page-2', 'name' => 'Nieuws overzicht'],
            ]);

        $listener = new CheckReferencedListener($this->searchContentReferenced);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Cannot remove referenced document');
        $this->expectExceptionMessage('Gelato update');
        $this->expectExceptionMessage('Homepage teaser (page-1)');
        $this->expectExceptionMessage('Nieuws overzicht (page-2)');

        $listener->preRemove($this->createLifecycleEvent($document));
    }

    private function createLifecycleEvent(object $document): LifecycleEventArgs
    {
        return new LifecycleEventArgs(
            $document,
            $this->createMock(DocumentManager::class)
        );
    }
}
