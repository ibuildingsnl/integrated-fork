<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Document\Page;

use Integrated\Bundle\PageBundle\Document\Page\PageEditDraft;
use PHPUnit\Framework\TestCase;

final class PageEditDraftTest extends TestCase
{
    public function testConstructInitializesIdentifiersAndTimestamps(): void
    {
        $draft = new PageEditDraft('page-1', 'user-1');

        self::assertSame('page-1', $draft->getPageId());
        self::assertSame('user-1', $draft->getUserId());
        self::assertInstanceOf(\DateTime::class, $draft->getCreatedAt());
        self::assertInstanceOf(\DateTime::class, $draft->getUpdatedAt());
        self::assertSame([], $draft->getGridPayload());
        self::assertSame([], $draft->getMenuPayload());
    }

    public function testPayloadAndBaselineCanBeUpdated(): void
    {
        $draft = new PageEditDraft('page-1', 'user-1');

        $draft->setGridPayload(['grid' => [['block' => 'a']]]);
        $draft->setMenuPayload(['menu' => [['name' => 'Main']]]);
        $draft->setBasePageUpdatedAt('2026-03-10T09:00:00+00:00');

        self::assertSame([['block' => 'a']], $draft->getGridPayload()['grid']);
        self::assertSame([['name' => 'Main']], $draft->getMenuPayload()['menu']);
        self::assertSame('2026-03-10T09:00:00+00:00', $draft->getBasePageUpdatedAt());
    }

    public function testVersionPushAndPruneRespectMaximum(): void
    {
        $draft = new PageEditDraft('page-1', 'user-1');
        $draft->pushVersion(['grid' => ['one']], ['menu' => ['one']], 50);
        $draft->pushVersion(['grid' => ['two']], ['menu' => ['two']], 50);
        $draft->pushVersion(['grid' => ['three']], ['menu' => ['three']], 50);

        self::assertCount(3, $draft->getVersions());
        self::assertSame(['three'], $draft->getVersions()[0]->getGridPayload()['grid']);

        $removed = $draft->pruneVersions(new \DateTimeImmutable('-30 days'), 2);

        self::assertSame(1, $removed);
        self::assertCount(2, $draft->getVersions());
    }
}
