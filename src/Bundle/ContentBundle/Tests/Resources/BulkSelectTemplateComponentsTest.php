<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BulkSelectTemplateComponentsTest extends TestCase
{
    public function testBulkSelectUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/bulk/select.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("component('integrated_admin:options_toolbar'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }

    public function testBulkCategorySelectionPartialUsesTaxonomyCategoryPicker(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/bulk/partial/category_selection_component.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:taxonomy_category_picker'", $template);
        self::assertStringContainsString("rootClass: 'bulk-taxonomy-category'", $template);
        self::assertStringContainsString('hiddenContentHtml: bulkCategorySelectionHiddenContentHtml', $template);
    }
}
