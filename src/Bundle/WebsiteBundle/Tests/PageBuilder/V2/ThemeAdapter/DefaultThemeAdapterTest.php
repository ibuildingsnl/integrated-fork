<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\PageBuilder\V2\ThemeAdapter;

use Integrated\Bundle\WebsiteBundle\PageBuilder\V2\ThemeAdapter\DefaultThemeAdapter;
use PHPUnit\Framework\TestCase;

final class DefaultThemeAdapterTest extends TestCase
{
    public function testSupportsOnlyDefaultTheme(): void
    {
        $adapter = new DefaultThemeAdapter();

        self::assertTrue($adapter->supportsTheme('default'));
        self::assertFalse($adapter->supportsTheme('twindigital'));
        self::assertFalse($adapter->supportsTheme('unknown-theme'));
    }

    public function testResolvesKnownComponentTemplates(): void
    {
        $adapter = new DefaultThemeAdapter();

        self::assertSame(
            '@IntegratedWebsite/themes/default/pagebuilder/components/container.html.twig',
            $adapter->resolveTemplateForComponent('container')
        );
        self::assertSame(
            '@IntegratedWebsite/themes/default/pagebuilder/components/block_ref.html.twig',
            $adapter->resolveTemplateForComponent('block_ref')
        );
        self::assertNull($adapter->resolveTemplateForComponent('unknown'));
    }
}

