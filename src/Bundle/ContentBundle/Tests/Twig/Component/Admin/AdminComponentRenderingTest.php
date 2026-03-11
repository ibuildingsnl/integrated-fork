<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Twig\Component\Admin;

use Integrated\Bundle\ContentBundle\Tests\Support\AdminComponentKernelTestCase;
use Integrated\Bundle\ContentBundle\Tests\Support\PaginationFooterTestPager;
use PHPUnit\Framework\Attributes\Test;

final class AdminComponentRenderingTest extends AdminComponentKernelTestCase
{
    #[Test]
    public function itRendersStatusBadgeMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:status_badge', [
            'label' => 'Sent',
            'variant' => 'sent',
            'title' => 'Mail status',
        ])->toString();

        self::assertStringContainsString('status-badge', $output);
        self::assertStringContainsString('status-sent', $output);
        self::assertStringContainsString('Sent', $output);
        self::assertStringContainsString('Mail status', $output);
    }

    #[Test]
    public function itRendersInfoStatusBadgeMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:status_badge', [
            'label' => 'System page',
            'variant' => 'info',
        ])->toString();

        self::assertStringContainsString('status-badge', $output);
        self::assertStringContainsString('status-info', $output);
        self::assertStringContainsString('System page', $output);
    }

    #[Test]
    public function itRendersWorkflowStatusMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:workflow_status', [
            'color' => '#00ae93',
            'icon' => 'check',
            'title' => 'Published',
        ])->toString();

        self::assertStringContainsString('workflow-status', $output);
        self::assertStringContainsString('iconoir-check', $output);
        self::assertStringContainsString('background-color:rgba(0,174,147,0.12)', str_replace(' ', '', $output));
        self::assertStringContainsString('title="Published"', $output);
    }

    #[Test]
    public function itRendersEmptyStateMessageMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:empty_state_message', [
            'tag' => 'li',
            'message' => 'No users found',
            'variant' => 'danger',
            'elementAttributes' => 'id="empty-users"',
            'wrapperClass' => 'group-users-empty',
        ])->toString();

        self::assertStringContainsString('<li', $output);
        self::assertStringContainsString('empty-state-message', $output);
        self::assertStringContainsString('empty-state-message-danger', $output);
        self::assertStringContainsString('group-users-empty', $output);
        self::assertStringContainsString('id="empty-users"', $output);
        self::assertStringContainsString('No users found', $output);
    }

    #[Test]
    public function itRendersDismissibleAlertBoxMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:alert_box', [
            'variant' => 'warning',
            'dismissible' => true,
            'bodyHtml' => '<strong>Heads up</strong>',
            'extraClass' => 'alert-inline',
        ])->toString();

        self::assertStringContainsString('alert alert-warning alert-dismissible alert-inline', $output);
        self::assertStringContainsString('class="close"', $output);
        self::assertStringContainsString('aria-label="Close notification"', $output);
        self::assertStringContainsString('<strong>Heads up</strong>', $output);
    }

    #[Test]
    public function itFallsBackToSafeAlertBoxTagMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:alert_box', [
            'tag' => 'ol',
            'variant' => 'danger',
            'bodyHtml' => '<p>Fallback tag</p>',
        ])->toString();

        self::assertStringContainsString('<div class="alert alert-danger">', $output);
        self::assertStringNotContainsString('<ol', $output);
        self::assertStringContainsString('<p>Fallback tag</p>', $output);
    }

    #[Test]
    public function itRendersSectionCardMarkupWithContentBlock(): void
    {
        $output = $this->renderTwigComponent(
            'integrated_admin:section_card',
            [
                'title' => 'Overview',
                'subtitle' => 'Latest state',
                'padding' => true,
            ],
            '<p>Body content</p>'
        )->toString();

        self::assertStringContainsString('section-white', $output);
        self::assertStringContainsString('section-radius', $output);
        self::assertStringContainsString('p-4', $output);
        self::assertStringContainsString('Overview', $output);
        self::assertStringContainsString('Latest state', $output);
        self::assertStringContainsString('<p>Body content</p>', $output);
    }

    #[Test]
    public function itFallsBackToSafeSectionCardTagMarkup(): void
    {
        $output = $this->renderTwigComponent(
            'integrated_admin:section_card',
            [
                'tag' => 'main',
            ],
            '<p>Body content</p>'
        )->toString();

        self::assertStringContainsString('<section class="section-white section-radius">', $output);
        self::assertStringNotContainsString('<main', $output);
        self::assertStringContainsString('<p>Body content</p>', $output);
    }

    #[Test]
    public function itRendersPageTitleMarkupWithActions(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:page_title', [
            'title' => 'Content',
            'subtitle' => 'Manage records',
            'actionsHtml' => '<a class="btn btn-primary" href="/admin/content/create">Create</a>',
        ])->toString();

        self::assertStringContainsString('page-title', $output);
        self::assertStringContainsString('<h1', $output);
        self::assertStringContainsString('Content', $output);
        self::assertStringContainsString('Manage records', $output);
        self::assertStringContainsString('btn btn-primary', $output);
    }

    #[Test]
    public function itFallsBackToSafePageTitleHeadingMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:page_title', [
            'title' => 'Content',
            'headingTag' => 'div',
        ])->toString();

        self::assertStringContainsString('<h1 class="heading">Content</h1>', $output);
        self::assertStringNotContainsString('<div class="heading">Content</div>', $output);
    }

    #[Test]
    public function itRendersPageTitleMarkupWithNestedContentHtml(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:page_title', [
            'title' => 'Mail accounts',
            'wrapperClass' => 'page-title--mail',
            'contentHtml' => '<div class="options options-toolbar"><ul class="options-no-bg"><li>Toolbar</li></ul></div>',
        ])->toString();

        self::assertStringContainsString('page-title page-title--mail', $output);
        self::assertStringContainsString('<h1', $output);
        self::assertStringContainsString('Mail accounts', $output);
        self::assertStringContainsString('options options-toolbar', $output);
        self::assertStringContainsString('options-no-bg', $output);
    }

    #[Test]
    public function itRendersPageTitleMarkupWithCustomTitleHtml(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:page_title', [
            'titleHtml' => '<div class="heading-switcher"><h1 class="heading">Content navigator</h1><div class="switcher-buttons">Switcher</div></div>',
            'actionsHtml' => '<button class="btn btn-orange">Filter</button>',
        ])->toString();

        self::assertStringContainsString('page-title', $output);
        self::assertStringContainsString('heading-switcher', $output);
        self::assertStringContainsString('switcher-buttons', $output);
        self::assertStringContainsString('btn btn-orange', $output);
    }

    #[Test]
    public function itRendersOptionsToolbarMarkupFromListItems(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:options_toolbar', [
            'wrapperClass' => 'toolbar-inline',
            'listItems' => [
                ['label' => 'Overview', 'href' => '/admin/content'],
                ['label' => 'Settings', 'href' => '/admin/settings', 'active' => true],
            ],
        ])->toString();

        self::assertStringContainsString('options options-toolbar toolbar-inline', $output);
        self::assertStringContainsString('content-navigator-menu', $output);
        self::assertStringContainsString('Overview', $output);
        self::assertStringContainsString('/admin/settings', $output);
    }

    #[Test]
    public function itRendersOptionsToolbarMarkupFromContentBlock(): void
    {
        $output = $this->renderTwigComponent(
            'integrated_admin:options_toolbar',
            [],
            '<ul class="content-navigator-menu"><li><button class="btn btn-select">Tools</button></li></ul>'
        )->toString();

        self::assertStringContainsString('options options-toolbar', $output);
        self::assertStringContainsString('content-navigator-menu', $output);
        self::assertStringContainsString('btn btn-select', $output);
    }

    #[Test]
    public function itRendersDataTableMarkupWithRows(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:data_table', [
            'colGroupHtml' => '<colgroup><col /><col /></colgroup>',
            'headHtml' => '<tr><th>Name</th><th>Status</th></tr>',
            'bodyHtml' => '<tr class="has-options"><td>Example</td><td>Draft</td></tr>',
            'tableClass' => 'table-sm',
        ])->toString();

        self::assertStringContainsString('<table', $output);
        self::assertStringContainsString('table table-hover table-sm', $output);
        self::assertStringContainsString('<colgroup><col /><col /></colgroup>', $output);
        self::assertStringContainsString('<thead>', $output);
        self::assertStringContainsString('<tbody>', $output);
        self::assertStringContainsString('has-options', $output);
    }

    #[Test]
    public function itRendersDataTableEmptyStateMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:data_table', [
            'headHtml' => '<tr><th>Name</th><th>Status</th></tr>',
            'emptyMessage' => 'No rows found',
            'colSpan' => 2,
            'hover' => false,
        ])->toString();

        self::assertStringContainsString('<table', $output);
        self::assertStringContainsString('table"', $output);
        self::assertStringNotContainsString('table-hover', $output);
        self::assertStringContainsString('colspan="2"', $output);
        self::assertStringContainsString('No rows found', $output);
    }

    #[Test]
    public function itRendersDetailListMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:detail_list', [
            'rows' => [
                ['term' => 'Name', 'valueHtml' => 'Example'],
                ['termHtml' => '<strong>Status</strong>', 'valueHtml' => '<span>Active</span>'],
            ],
            'extraClass' => 'detail-list-compact',
        ])->toString();

        self::assertStringContainsString('<dl class="dl-horizontal detail-list-compact">', $output);
        self::assertStringContainsString('<dt>', $output);
        self::assertStringContainsString('Name', $output);
        self::assertStringContainsString('<strong>Status</strong>', $output);
        self::assertStringContainsString('<span>Active</span>', $output);
    }

    #[Test]
    public function itRendersEditFormShellMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:edit_form_shell', [
            'shellId' => 'channel-config-form',
            'extraClass' => 'edit-form--channel-config',
            'toolbarHtml' => '<div class="block-toolbar">Toolbar</div>',
            'sidebarContentHtml' => '<aside><div class="aside-holder"><div class="aside-header"><span>Options</span></div><div class="aside-item-wrapper">Sidebar</div></div></aside>',
            'editorWrapperClass' => 'editor-wrapper-full',
            'editorWrapperAttributes' => 'data-panel="edit-shell"',
            'pageTitleHtml' => '<div class="page-title"><div class="heading"><h1 class="heading">Edit relation</h1></div></div>',
            'editorContentHtml' => '<div class="form-body">Editor</div>',
            'editorClass' => 'relation-editor',
            'afterEditorHtml' => '<div class="after-editor">After</div>',
        ])->toString();

        self::assertStringContainsString('id="channel-config-form"', $output);
        self::assertStringContainsString('flex flex-wrap edit-form edit-form--channel-config', $output);
        self::assertStringContainsString('<div class="block-toolbar">Toolbar</div>', $output);
        self::assertStringContainsString('<div class="aside-options">', $output);
        self::assertStringContainsString('aside-holder', $output);
        self::assertStringContainsString('<div class="editor-wrapper editor-wrapper-full" data-panel="edit-shell">', $output);
        self::assertStringContainsString('<div class="page-title">', $output);
        self::assertStringContainsString('<section class="editor editor-wrapped relation-editor">', $output);
        self::assertStringContainsString('<div class="form-body">Editor</div>', $output);
        self::assertStringContainsString('<div class="after-editor">After</div>', $output);
    }

    #[Test]
    public function itRendersConfirmModalMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:confirm_modal', [
            'modalId' => 'content-edit-modal',
            'title' => 'Unsaved changes',
            'bodyHtml' => '<p>Leave this page?</p>',
            'cancelHtml' => '<button type="button" class="btn btn-white" data-dismiss="modal">Stay</button>',
            'confirmHtml' => '<button type="button" class="btn btn-dark-green live-page">Leave</button>',
            'modalAttributes' => 'style="display: none;"',
        ])->toString();

        self::assertStringContainsString('modal fade', $output);
        self::assertStringContainsString('id="content-edit-modal"', $output);
        self::assertStringContainsString('style="display: none;"', $output);
        self::assertStringContainsString('modal-title">Unsaved changes</h4>', $output);
        self::assertStringContainsString('<div class="modal-body">', $output);
        self::assertStringContainsString('Leave this page?', $output);
        self::assertStringContainsString('btn btn-white', $output);
        self::assertStringContainsString('btn btn-dark-green live-page', $output);
    }

    #[Test]
    public function itRendersDataTableInsideOptionalWrapper(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:data_table', [
            'headHtml' => '<tr><th>Name</th></tr>',
            'bodyHtml' => '<tr><td>Example</td></tr>',
            'wrapperClass' => 'table-responsive consent-manager-live-table',
            'extraClass' => 'table-sm',
        ])->toString();

        self::assertStringContainsString('<div class="table-responsive consent-manager-live-table">', $output);
        self::assertStringContainsString('table table-hover table-sm', $output);
        self::assertStringContainsString('<td>Example</td>', $output);
    }

    #[Test]
    public function itRendersDataTableTbodyAttributes(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:data_table', [
            'headHtml' => '<tr><th>Name</th></tr>',
            'bodyHtml' => '<tr><td>Example</td></tr>',
            'tbodyAttributes' => 'id="post-list" data-list="content"',
        ])->toString();

        self::assertStringContainsString('<tbody id="post-list" data-list="content">', $output);
        self::assertStringContainsString('<td>Example</td>', $output);
    }

    #[Test]
    public function itRendersAsidePanelMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:aside_panel', [
            'title' => 'Filters',
            'icon' => 'filter-alt',
            'expanded' => true,
            'wrapperClass' => 'filters-panel',
            'containerClass' => 'filters-panel-body',
            'contentHtml' => '<ul class="filters_list"><li>Example</li></ul>',
        ])->toString();

        self::assertStringContainsString('aside-item-holder', $output);
        self::assertStringContainsString('aside-item-wrapper show filters-panel', $output);
        self::assertStringContainsString('aria-expanded="true"', $output);
        self::assertStringContainsString('iconoir-filter-alt', $output);
        self::assertStringContainsString('filters-panel-body', $output);
        self::assertStringContainsString('filters_list', $output);
    }

    #[Test]
    public function itCanRenderAsidePanelWithoutHolderWrapper(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:aside_panel', [
            'title' => 'Status',
            'icon' => 'info-circle',
            'expanded' => true,
            'withHolder' => false,
            'contentHtml' => '<div class="status-info">Example</div>',
        ])->toString();

        self::assertStringNotContainsString('aside-item-holder', $output);
        self::assertStringContainsString('aside-item-wrapper show', $output);
        self::assertStringContainsString('status-info', $output);
    }

    #[Test]
    public function itRendersFilterGroupMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:filter_group', [
            'title' => 'Channels',
            'wrapperClass' => 'channel-group',
            'listClass' => 'form-group overflow-y-auto',
            'containerClass' => 'w-full',
            'listStyle' => 'max-height: 24rem;',
            'contentHtml' => '<ul class="filters_list"><li>Website</li></ul>',
        ])->toString();

        self::assertStringContainsString('aside-item-container channel-group', $output);
        self::assertStringContainsString('aside-item-header', $output);
        self::assertStringContainsString('Channels', $output);
        self::assertStringContainsString('aside-item-list form-group overflow-y-auto', $output);
        self::assertStringContainsString('style="max-height: 24rem;"', $output);
        self::assertStringContainsString('filters_list', $output);
    }

    #[Test]
    public function itRendersFilterSearchInputMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:filter_search_input', [
            'inputHtml' => '<input class="form-control" type="search" value="query" />',
            'buttonHtml' => '<button type="submit"><i class="iconoir-search"></i></button>',
            'holderClass' => 'search-holder',
        ])->toString();

        self::assertStringContainsString('aside-item-holder search-holder', $output);
        self::assertStringContainsString('input-group input-group-search', $output);
        self::assertStringContainsString('form-control', $output);
        self::assertStringContainsString('input-group-btn', $output);
        self::assertStringContainsString('iconoir-search', $output);
    }

    #[Test]
    public function itRendersFolderMenuPanelMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:folder_menu_panel', [
            'title' => 'Folders',
            'holderClass' => 'bg-white section-radius',
            'rootLinkHtml' => '<a href="/admin/media"><i class="iconoir-folder"></i><span class="text-black">All files</span></a>',
            'searchHtml' => '<div class="aside-item-search"><i class="iconoir-search"></i><input type="text" class="list-search" /></div>',
            'contentHtml' => '<ul class="aside-filter-menu"><li class="menu-parent">Folder</li></ul>',
        ])->toString();

        self::assertStringContainsString('aside-header', $output);
        self::assertStringContainsString('Folders', $output);
        self::assertStringContainsString('aside-holder bg-white section-radius', $output);
        self::assertStringContainsString('aside-folder-header', $output);
        self::assertStringContainsString('iconoir-folder', $output);
        self::assertStringContainsString('aside-item-search', $output);
        self::assertStringContainsString('aside-filter-menu', $output);
    }

    #[Test]
    public function itRendersEditDrawerPanelMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:edit_drawer_panel', [
            'editImagePath' => '/admin/media/edit/REPLACE',
            'editImageIframePath' => '/admin/media/edit/iframe/REPLACE',
        ])->toString();

        self::assertStringContainsString('aside-edit-holder bg-white hide', $output);
        self::assertStringContainsString('aside-item-wrapper', $output);
        self::assertStringContainsString('id="editimagewrapper"', $output);
        self::assertStringContainsString('data-editImagePath="/admin/media/edit/REPLACE"', $output);
        self::assertStringContainsString('data-editImageIframePath="/admin/media/edit/iframe/REPLACE"', $output);
        self::assertStringContainsString('close-media-edit-form', $output);
        self::assertStringContainsString('<turbo-frame id="media-edit-panel"', $output);
    }

    #[Test]
    public function itRendersIframeModalMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:iframe_modal', [
            'modalId' => 'navigator-edit-modal',
            'title' => 'Edit',
            'iframeId' => 'editmodaliframe',
            'iframeSrc' => '/admin/content/edit/123',
            'dialogClass' => 'modal-lg',
        ])->toString();

        self::assertStringContainsString('modal add-modal close-outside', $output);
        self::assertStringContainsString('id="navigator-edit-modal"', $output);
        self::assertStringContainsString('modal-dialog modal-lg', $output);
        self::assertStringContainsString('modal-title">Edit</h4>', $output);
        self::assertStringContainsString('id="editmodaliframe"', $output);
        self::assertStringContainsString('src="/admin/content/edit/123"', $output);
    }

    #[Test]
    public function itRendersPaginationFooterMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:pagination_footer', [
            'pagination' => new PaginationFooterTestPager(),
            'wrapperClass' => 'table-pagination mt-4',
        ])->toString();

        self::assertStringContainsString('<div class="table-pagination mt-4">', $output);
        self::assertStringContainsString('pagination-page-1', $output);
        self::assertStringContainsString('pagination-page-2', $output);
    }

    #[Test]
    public function itRendersTaxonomyCategoryPickerMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:taxonomy_category_picker', [
            'rootId' => 'taxonomy_category_topics',
            'rootClass' => 'bulk-taxonomy-category',
            'relationTitle' => 'Topics',
            'categories' => [
                ['depth' => 0, 'title' => 'Topics', 'taxonomyId' => 'topics', 'linkToChannel' => ''],
                ['depth' => 1, 'title' => 'News', 'taxonomyId' => 'news', 'linkToChannel' => ''],
            ],
            'checkboxIdPrefix' => 'relation_1_',
            'allTabActive' => true,
            'hiddenContentHtml' => '<input type="hidden" class="relation-items" value="news" />',
        ])->toString();

        self::assertStringContainsString('taxonomy_category bulk-taxonomy-category', $output);
        self::assertStringContainsString('id="taxonomy_category_topics"', $output);
        self::assertStringContainsString('category_tab active', $output);
        self::assertStringContainsString('aside-item-search', $output);
        self::assertStringContainsString('Current Selection:', $output);
        self::assertStringContainsString('for="relation_1_topics"', $output);
        self::assertStringContainsString('class="relation-items"', $output);
    }

    #[Test]
    public function itRendersRowActionsMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:row_actions', [
            'contentHtml' => '<a href="/admin/example/1/edit">Edit</a> | <a class="color-red" href="/admin/example/1/delete">Delete</a>',
            'wrapperClass' => 'compact-actions',
        ])->toString();

        self::assertStringContainsString('<div class="row-options compact-actions">', $output);
        self::assertStringContainsString('/admin/example/1/edit', $output);
        self::assertStringContainsString('color-red', $output);
    }

    #[Test]
    public function itRendersSelectionModalMarkup(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:selection_modal', [
            'modalId' => 'group-users-add-modal',
            'title' => 'Select users',
            'bodyHtml' => '<label class="control-label">Select users</label><select class="form-control"></select>',
            'footerHtml' => '<button type="button" class="btn btn-white">Cancel</button><button type="button" class="btn btn-green">Add</button>',
            'modalAttributes' => 'style="display:none;"',
            'bodyClass' => 'p-4',
            'closeButtonAttributes' => 'data-action="close-group-users-modal"',
        ])->toString();

        self::assertStringContainsString('modal add-modal close-outside', $output);
        self::assertStringContainsString('id="group-users-add-modal"', $output);
        self::assertStringContainsString('style="display:none;"', $output);
        self::assertStringContainsString('modal-title">Select users</h4>', $output);
        self::assertStringContainsString('modal-body p-4', $output);
        self::assertStringContainsString('close-group-users-modal', $output);
        self::assertStringContainsString('btn btn-green', $output);
    }
}
