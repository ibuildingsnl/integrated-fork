<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Support;

use Integrated\Bundle\ContentBundle\Twig\Component\Admin\AlertBox;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\AsidePanel;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\ConfirmModal;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\DataTable;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\DetailList;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\EmptyStateMessage;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\EditDrawerPanel;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\EditFormShell;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\FilterGroup;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\FilterSearchInput;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\FolderMenuPanel;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\IframeModal;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\OptionsToolbar;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\PageTitle;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\PaginationFooter;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\RowActions;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\SectionCard;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\SelectionModal;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\StatusBadge;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\TaxonomyCategoryPicker;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\WorkflowStatus;

final class AdminComponentContract
{
    /**
     * @return array<string, array{class: class-string, template: string}>
     */
    public static function publicComponents(): array
    {
        return [
            'integrated_admin:alert_box' => [
                'class' => AlertBox::class,
                'template' => '@IntegratedContent/components/admin/alert_box.html.twig',
            ],
            'integrated_admin:aside_panel' => [
                'class' => AsidePanel::class,
                'template' => '@IntegratedContent/components/admin/aside_panel.html.twig',
            ],
            'integrated_admin:confirm_modal' => [
                'class' => ConfirmModal::class,
                'template' => '@IntegratedContent/components/admin/confirm_modal.html.twig',
            ],
            'integrated_admin:data_table' => [
                'class' => DataTable::class,
                'template' => '@IntegratedContent/components/admin/data_table.html.twig',
            ],
            'integrated_admin:detail_list' => [
                'class' => DetailList::class,
                'template' => '@IntegratedContent/components/admin/detail_list.html.twig',
            ],
            'integrated_admin:empty_state_message' => [
                'class' => EmptyStateMessage::class,
                'template' => '@IntegratedContent/components/admin/empty_state_message.html.twig',
            ],
            'integrated_admin:edit_drawer_panel' => [
                'class' => EditDrawerPanel::class,
                'template' => '@IntegratedContent/components/admin/edit_drawer_panel.html.twig',
            ],
            'integrated_admin:edit_form_shell' => [
                'class' => EditFormShell::class,
                'template' => '@IntegratedContent/components/admin/edit_form_shell.html.twig',
            ],
            'integrated_admin:filter_group' => [
                'class' => FilterGroup::class,
                'template' => '@IntegratedContent/components/admin/filter_group.html.twig',
            ],
            'integrated_admin:filter_search_input' => [
                'class' => FilterSearchInput::class,
                'template' => '@IntegratedContent/components/admin/filter_search_input.html.twig',
            ],
            'integrated_admin:folder_menu_panel' => [
                'class' => FolderMenuPanel::class,
                'template' => '@IntegratedContent/components/admin/folder_menu_panel.html.twig',
            ],
            'integrated_admin:iframe_modal' => [
                'class' => IframeModal::class,
                'template' => '@IntegratedContent/components/admin/iframe_modal.html.twig',
            ],
            'integrated_admin:options_toolbar' => [
                'class' => OptionsToolbar::class,
                'template' => '@IntegratedContent/components/admin/options_toolbar.html.twig',
            ],
            'integrated_admin:page_title' => [
                'class' => PageTitle::class,
                'template' => '@IntegratedContent/components/admin/page_title.html.twig',
            ],
            'integrated_admin:pagination_footer' => [
                'class' => PaginationFooter::class,
                'template' => '@IntegratedContent/components/admin/pagination_footer.html.twig',
            ],
            'integrated_admin:row_actions' => [
                'class' => RowActions::class,
                'template' => '@IntegratedContent/components/admin/row_actions.html.twig',
            ],
            'integrated_admin:section_card' => [
                'class' => SectionCard::class,
                'template' => '@IntegratedContent/components/admin/section_card.html.twig',
            ],
            'integrated_admin:selection_modal' => [
                'class' => SelectionModal::class,
                'template' => '@IntegratedContent/components/admin/selection_modal.html.twig',
            ],
            'integrated_admin:status_badge' => [
                'class' => StatusBadge::class,
                'template' => '@IntegratedContent/components/admin/status_badge.html.twig',
            ],
            'integrated_admin:taxonomy_category_picker' => [
                'class' => TaxonomyCategoryPicker::class,
                'template' => '@IntegratedContent/components/admin/taxonomy_category_picker.html.twig',
            ],
            'integrated_admin:workflow_status' => [
                'class' => WorkflowStatus::class,
                'template' => '@IntegratedContent/components/admin/workflow_status.html.twig',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function lightweightTestComponents(): array
    {
        return [
            'integrated_admin:alert_box',
            'integrated_admin:data_table',
            'integrated_admin:options_toolbar',
            'integrated_admin:page_title',
            'integrated_admin:pagination_footer',
            'integrated_admin:row_actions',
            'integrated_admin:section_card',
            'integrated_admin:status_badge',
        ];
    }

    /**
     * @return list<class-string>
     */
    public static function componentClasses(): array
    {
        return array_values(array_map(
            static fn (array $component): string => $component['class'],
            self::publicComponents()
        ));
    }

    /**
     * @return array<string, string>
     */
    public static function componentTemplates(): array
    {
        $templates = [];

        foreach (self::publicComponents() as $key => $component) {
            $templates[$key] = $component['template'];
        }

        return $templates;
    }
}
