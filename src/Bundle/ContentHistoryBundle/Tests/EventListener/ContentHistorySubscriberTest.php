<?php

namespace Integrated\Bundle\ContentHistoryBundle\Tests\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentHistoryBundle\Document\ContentHistory;
use Integrated\Bundle\ContentHistoryBundle\Document\Embedded\Request;
use Integrated\Bundle\ContentHistoryBundle\Document\Embedded\User;
use Integrated\Bundle\ContentHistoryBundle\Event\ContentHistoryEvent;
use Integrated\Bundle\ContentHistoryBundle\EventListener\ContentHistorySubscriber;
use Integrated\Common\Content\ContentInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContentHistorySubscriberTest extends TestCase
{
    public function testShouldSkipDuplicateUpdateWhenSameChangeSetWithinTwoSeconds(): void
    {
        $current = $this->createHistory(['title' => ['old', 'new']], 1700000001);
        $last = $this->createHistory(['title' => ['old', 'new']], 1700000000);

        $repository = $this->createMock(DocumentRepository::class);
        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($last);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(self::once())
            ->method('getRepository')
            ->willReturn($repository);

        $subscriber = new ContentHistorySubscriber(
            $this->createMock(EventDispatcherInterface::class),
            ContentHistory::class
        );

        $result = $this->invokePrivate($subscriber, 'shouldSkipDuplicateUpdate', [
            $documentManager,
            $current,
            ContentHistoryEvent::UPDATE,
        ]);

        self::assertTrue($result);
    }

    public function testShouldNotSkipWhenChangeSetDiffers(): void
    {
        $current = $this->createHistory(['title' => ['old', 'new']], 1700000001);
        $last = $this->createHistory(['title' => ['old', 'different']], 1700000000);

        $repository = $this->createMock(DocumentRepository::class);
        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($last);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(self::once())
            ->method('getRepository')
            ->willReturn($repository);

        $subscriber = new ContentHistorySubscriber(
            $this->createMock(EventDispatcherInterface::class),
            ContentHistory::class
        );

        $result = $this->invokePrivate($subscriber, 'shouldSkipDuplicateUpdate', [
            $documentManager,
            $current,
            ContentHistoryEvent::UPDATE,
        ]);

        self::assertFalse($result);
    }

    public function testShouldNotSkipWhenOlderThanTwoSeconds(): void
    {
        $current = $this->createHistory(['title' => ['old', 'new']], 1700000010);
        $last = $this->createHistory(['title' => ['old', 'new']], 1700000000);

        $repository = $this->createMock(DocumentRepository::class);
        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($last);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(self::once())
            ->method('getRepository')
            ->willReturn($repository);

        $subscriber = new ContentHistorySubscriber(
            $this->createMock(EventDispatcherInterface::class),
            ContentHistory::class
        );

        $result = $this->invokePrivate($subscriber, 'shouldSkipDuplicateUpdate', [
            $documentManager,
            $current,
            ContentHistoryEvent::UPDATE,
        ]);

        self::assertFalse($result);
    }

    public function testMergeChangeSetsCombinesOldAndNewValues(): void
    {
        $subscriber = new ContentHistorySubscriber(
            $this->createMock(EventDispatcherInterface::class),
            ContentHistory::class
        );

        $result = $this->invokePrivate($subscriber, 'mergeChangeSets', [
            [
                'title' => ['old', 'new'],
                'status' => ['draft', 'review'],
            ],
            [
                'status' => ['review', 'published'],
                'relations' => ['authors' => [['old-author'], ['new-author']]],
            ],
        ]);

        self::assertSame(
            [
                'title' => ['old', 'new'],
                'status' => ['draft', 'published'],
                'relations' => ['authors' => [['old-author'], ['new-author']]],
            ],
            $result
        );
    }

    private function createHistory(array $changeSet, int $timestamp, ?string $requestId = null): ContentHistory
    {
        $content = $this->createMock(ContentInterface::class);
        $content->method('getId')->willReturn('content-id');
        $content->method('getContentType')->willReturn('article');

        $history = new ContentHistory($content, ContentHistoryEvent::UPDATE);
        $history->setChangeSet($changeSet);

        $request = new Request();
        $request->setRequestId($requestId);
        $request->setEndpoint('https://localhost/admin/content/edit');
        $history->setRequest($request);

        $user = new User();
        $user->setId(1);
        $user->setName('admin');
        $history->setUser($user);

        $reflection = new \ReflectionClass($history);
        $dateProperty = $reflection->getProperty('date');
        $dateProperty->setAccessible(true);
        $dateProperty->setValue($history, (new \DateTime())->setTimestamp($timestamp));

        return $history;
    }

    private function invokePrivate(object $instance, string $method, array $args = []): mixed
    {
        $reflection = new \ReflectionClass($instance);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($instance, $args);
    }
}
