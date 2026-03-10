<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Twig\Component\Admin;

use Integrated\Bundle\ContentBundle\Twig\Component\Admin\SectionCard;
use PHPUnit\Framework\TestCase;

final class SectionCardComponentTest extends TestCase
{
    public function testUsesSectionTagByDefault(): void
    {
        $component = new SectionCard();

        self::assertSame('section', $component->tag);
    }

    public function testUsesBaseSurfaceClassesByDefault(): void
    {
        $component = new SectionCard();

        self::assertSame('section-white section-radius', $component->surfaceClass());
    }

    public function testAddsPaddingClassWhenEnabled(): void
    {
        $component = new SectionCard();
        $component->padding = true;

        self::assertSame('section-white section-radius p-4', $component->surfaceClass());
    }
}
