<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class SearchSelectionIndexTemplateTest extends TestCase
{
    public function testIndexTemplateShowsSortAndOrderColumnsIncludingCustomSortHandling(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/search_selection/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('{% trans %}Sort by{% endtrans %}', $template);
        self::assertStringContainsString('{% trans %}Sort order{% endtrans %}', $template);
        self::assertStringContainsString("sortField starts with('custom:')", $template);
        self::assertStringContainsString('{% trans %}Custom sorting{% endtrans %}', $template);
    }

    public function testIndexTemplateUsesAdminPageTitleAndSectionCardComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/search_selection/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
        self::assertStringContainsString("component('integrated_admin:pagination_footer'", $template);
        self::assertStringContainsString("component('integrated_admin:empty_state_message'", $template);
    }
}
