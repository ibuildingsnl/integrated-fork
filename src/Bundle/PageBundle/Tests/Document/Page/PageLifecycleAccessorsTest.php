<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Document\Page;

use Integrated\Bundle\PageBundle\Document\Page\Page;
use PHPUnit\Framework\TestCase;

final class PageLifecycleAccessorsTest extends TestCase
{
    public function testLifecycleFieldsDefaultToNull(): void
    {
        $page = new Page();

        self::assertNull($page->getPublishAt());
        self::assertNull($page->getExpireAt());
        self::assertNull($page->getExpireRedirectUrl());
        self::assertNull($page->isHideFromSitemap());
    }

    public function testLifecycleFieldsRoundTripValues(): void
    {
        $page = new Page();
        $publishAt = new \DateTimeImmutable('2026-03-27 10:15:00');
        $expireAt = new \DateTimeImmutable('2026-04-02 18:30:00');

        $page->setPublishAt($publishAt);
        $page->setExpireAt($expireAt);
        $page->setExpireRedirectUrl('/expired');
        $page->setHideFromSitemap(true);

        self::assertSame($publishAt, $page->getPublishAt());
        self::assertSame($expireAt, $page->getExpireAt());
        self::assertSame('/expired', $page->getExpireRedirectUrl());
        self::assertTrue($page->isHideFromSitemap());
    }

    public function testLifecycleFieldsCanBeClearedAgain(): void
    {
        $page = new Page();
        $page->setPublishAt(new \DateTimeImmutable('2026-03-27 10:15:00'));
        $page->setExpireAt(new \DateTimeImmutable('2026-04-02 18:30:00'));
        $page->setExpireRedirectUrl('https://example.com/expired');
        $page->setHideFromSitemap(false);

        $page->setPublishAt(null);
        $page->setExpireAt(null);
        $page->setExpireRedirectUrl(null);
        $page->setHideFromSitemap(null);

        self::assertNull($page->getPublishAt());
        self::assertNull($page->getExpireAt());
        self::assertNull($page->getExpireRedirectUrl());
        self::assertNull($page->isHideFromSitemap());
    }
}
