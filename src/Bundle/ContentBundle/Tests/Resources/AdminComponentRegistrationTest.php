<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class AdminComponentRegistrationTest extends TestCase
{
    public function testTwigConfigurationExplicitlyRegistersAdminComponents(): void
    {
        $config = file_get_contents(__DIR__.'/../../Resources/config/twig.xml');

        self::assertIsString($config);

        foreach ($this->expectedComponentMap() as $key => $template) {
            self::assertStringContainsString('key="'.$key.'"', $config);
            self::assertStringContainsString('template="'.$template.'"', $config);
        }
    }

    /**
     * @return array<string, string>
     */
    private function expectedComponentMap(): array
    {
        return [
            'integrated_admin:alert_box' => '@IntegratedContent/components/admin/alert_box.html.twig',
            'integrated_admin:aside_panel' => '@IntegratedContent/components/admin/aside_panel.html.twig',
            'integrated_admin:confirm_modal' => '@IntegratedContent/components/admin/confirm_modal.html.twig',
            'integrated_admin:data_table' => '@IntegratedContent/components/admin/data_table.html.twig',
            'integrated_admin:detail_list' => '@IntegratedContent/components/admin/detail_list.html.twig',
            'integrated_admin:edit_form_shell' => '@IntegratedContent/components/admin/edit_form_shell.html.twig',
            'integrated_admin:edit_drawer_panel' => '@IntegratedContent/components/admin/edit_drawer_panel.html.twig',
            'integrated_admin:filter_search_input' => '@IntegratedContent/components/admin/filter_search_input.html.twig',
            'integrated_admin:filter_group' => '@IntegratedContent/components/admin/filter_group.html.twig',
            'integrated_admin:folder_menu_panel' => '@IntegratedContent/components/admin/folder_menu_panel.html.twig',
            'integrated_admin:iframe_modal' => '@IntegratedContent/components/admin/iframe_modal.html.twig',
            'integrated_admin:options_toolbar' => '@IntegratedContent/components/admin/options_toolbar.html.twig',
            'integrated_admin:pagination_footer' => '@IntegratedContent/components/admin/pagination_footer.html.twig',
            'integrated_admin:page_title' => '@IntegratedContent/components/admin/page_title.html.twig',
            'integrated_admin:row_actions' => '@IntegratedContent/components/admin/row_actions.html.twig',
            'integrated_admin:selection_modal' => '@IntegratedContent/components/admin/selection_modal.html.twig',
            'integrated_admin:section_card' => '@IntegratedContent/components/admin/section_card.html.twig',
            'integrated_admin:status_badge' => '@IntegratedContent/components/admin/status_badge.html.twig',
            'integrated_admin:taxonomy_category_picker' => '@IntegratedContent/components/admin/taxonomy_category_picker.html.twig',
        ];
    }
}
