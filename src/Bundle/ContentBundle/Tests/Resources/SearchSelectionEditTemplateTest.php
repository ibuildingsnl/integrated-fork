<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SearchSelectionEditTemplateTest extends TestCase
{
    #[DataProvider('searchSelectionTemplateProvider')]
    public function testSearchSelectionEditTemplatesUseAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/search_selection/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    #[DataProvider('editFormShellTemplateProvider')]
    public function testSearchSelectionEditTemplatesUseAdminEditFormShellComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/search_selection/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    public static function searchSelectionTemplateProvider(): iterable
    {
        yield ['new.html.twig'];
        yield ['edit.html.twig'];
        yield ['delete.html.twig'];
    }

    public static function editFormShellTemplateProvider(): iterable
    {
        yield ['new.html.twig'];
        yield ['edit.html.twig'];
    }
}
