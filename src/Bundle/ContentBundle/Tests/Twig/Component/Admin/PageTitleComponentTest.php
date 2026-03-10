<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Twig\Component\Admin;

use Integrated\Bundle\ContentBundle\Twig\Component\Admin\PageTitle;
use PHPUnit\Framework\TestCase;

final class PageTitleComponentTest extends TestCase
{
    public function testUsesH1HeadingTagByDefault(): void
    {
        $component = new PageTitle();
        $component->title = 'Dashboard';

        self::assertSame('h1', $component->headingTag);
    }

    public function testUsesPageTitleClassByDefault(): void
    {
        $component = new PageTitle();
        $component->title = 'Dashboard';

        self::assertSame('page-title', $component->wrapperClass());
    }

    public function testAppendsExtraClassToWrapper(): void
    {
        $component = new PageTitle();
        $component->title = 'Dashboard';
        $component->extraClass = 'mb-4';

        self::assertSame('page-title mb-4', $component->wrapperClass());
    }
}
