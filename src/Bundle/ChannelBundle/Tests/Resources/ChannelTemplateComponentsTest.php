<?php

declare(strict_types=1);

namespace Integrated\Bundle\ChannelBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ChannelTemplateComponentsTest extends TestCase
{
    public function testConfigIndexUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/config/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }

    public function testChannelOptionsFormThemeUsesAdminSectionCardsForSocialConnectorLayout(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/options.html.twig');

        self::assertIsString($template);
        self::assertSame(3, substr_count($template, "{% component 'integrated_admin:section_card'"));
    }

    /**
     * @dataProvider configCrudTemplateProvider
     */
    public function testConfigCrudTemplatesUseAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    /**
     * @dataProvider configSidebarTemplateProvider
     */
    public function testConfigSidebarTemplatesUseAdminAsidePanel(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:aside_panel'", $template);
        self::assertStringNotContainsString("expanded: true", $template);
        self::assertStringNotContainsString("wrapperClass: 'show'", $template);
    }

    public static function configCrudTemplateProvider(): iterable
    {
        yield ['config/new.html.twig'];
        yield ['config/edit.html.twig'];
        yield ['config/delete.html.twig'];
    }

    public static function configSidebarTemplateProvider(): iterable
    {
        yield ['config/new.html.twig'];
        yield ['config/edit.html.twig'];
    }
}
