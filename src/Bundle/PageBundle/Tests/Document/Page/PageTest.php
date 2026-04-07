<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Document\Page;

use Integrated\Bundle\PageBundle\Document\Page\Page;
use PHPUnit\Framework\TestCase;

final class PageTest extends TestCase
{
    public function testPageDefaultsToSafeLayout(): void
    {
        $page = new Page();

        self::assertSame(Page::DEFAULT_LAYOUT, $page->getLayout());
    }

    public function testSetLayoutNormalizesEmptyValueToDefaultLayout(): void
    {
        $page = new Page();
        $page->setLayout('   ');

        self::assertSame(Page::DEFAULT_LAYOUT, $page->getLayout());
    }
}
