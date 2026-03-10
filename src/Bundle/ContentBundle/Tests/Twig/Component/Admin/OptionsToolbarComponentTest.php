<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Twig\Component\Admin;

use Integrated\Bundle\ContentBundle\Twig\Component\Admin\OptionsToolbar;
use PHPUnit\Framework\TestCase;

final class OptionsToolbarComponentTest extends TestCase
{
    public function testUsesContentNavigatorMenuByDefault(): void
    {
        $component = new OptionsToolbar();

        self::assertSame('content-navigator-menu', $component->menuClass);
    }

    public function testUsesIntegratedToolbarClassesByDefault(): void
    {
        $component = new OptionsToolbar();

        self::assertSame('options options-toolbar', $component->wrapperClass());
    }

    public function testAppendsExtraClassToWrapper(): void
    {
        $component = new OptionsToolbar();
        $component->extraClass = 'justify-between';

        self::assertSame('options options-toolbar justify-between', $component->wrapperClass());
    }
}
