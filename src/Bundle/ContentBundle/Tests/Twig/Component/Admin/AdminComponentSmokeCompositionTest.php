<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Twig\Component\Admin;

use Integrated\Bundle\ContentBundle\Tests\Support\AdminComponentKernelTestCase;
use Integrated\Bundle\ContentBundle\Tests\Support\PaginationFooterTestPager;
use PHPUnit\Framework\Attributes\Test;

final class AdminComponentSmokeCompositionTest extends AdminComponentKernelTestCase
{
    #[Test]
    public function itRendersAdminIndexComposition(): void
    {
        $output = $this->renderTwigString(<<<'TWIG'
{{ component('integrated_admin:page_title', {
    title: 'Pages',
    actionsHtml: '<button class="btn btn-create-new">New</button>',
    contentHtml: component('integrated_admin:options_toolbar', {
        listItems: [
            { label: 'All pages', href: '/admin/page' },
            { label: 'Drafts', href: '/admin/page?status=draft', active: true }
        ]
    })
}) }}
{% component 'integrated_admin:section_card' %}
    {% block content %}
        {{ component('integrated_admin:data_table', {
            headHtml: '<tr><th>Title</th><th>Status</th></tr>',
            bodyHtml: '<tr class="has-options"><td><strong>Example</strong>' ~ component('integrated_admin:row_actions', {
                contentHtml: '<a href="/admin/page/1/edit">Edit</a>'
            }) ~ '</td><td>' ~ component('integrated_admin:status_badge', {
                label: 'Published',
                variant: 'active'
            }) ~ '</td></tr>'
        }) }}
        {{ component('integrated_admin:pagination_footer', {
            pagination: pager,
            wrapperClass: 'table-pagination'
        }) }}
    {% endblock %}
{% endcomponent %}
TWIG, ['pager' => new PaginationFooterTestPager()]);

        self::assertStringContainsString('page-title', $output);
        self::assertStringContainsString('options options-toolbar', $output);
        self::assertStringContainsString('section-white section-radius', $output);
        self::assertStringContainsString('table table-hover', $output);
        self::assertStringContainsString('row-options', $output);
        self::assertStringContainsString('status-badge', $output);
        self::assertStringContainsString('pagination-test', $output);
    }

    #[Test]
    public function itRendersAdminEditorComposition(): void
    {
        $output = $this->renderTwigString(<<<'TWIG'
{{ component('integrated_admin:edit_form_shell', {
    shellId: 'editor-shell',
    toolbarHtml: component('integrated_admin:options_toolbar', {
        contentHtml: '<ul class="content-navigator-menu"><li><button class="btn btn-green">Save</button></li></ul>'
    }),
    sidebarContentHtml: component('integrated_admin:aside_panel', {
        title: 'Status',
        icon: 'info-circle',
        expanded: true,
        withHolder: false,
        contentHtml: component('integrated_admin:alert_box', {
            variant: 'info',
            bodyHtml: '<p>Draft</p>'
        })
    }),
    pageTitleHtml: component('integrated_admin:page_title', {
        title: 'Edit article'
    }),
    editorContentHtml: component('integrated_admin:detail_list', {
        rows: [
            { term: 'Slug', valueHtml: '/example-article' }
        ]
    })
}) }}
TWIG);

        self::assertStringContainsString('edit-form', $output);
        self::assertStringContainsString('options options-toolbar', $output);
        self::assertStringContainsString('aside-item-wrapper show', $output);
        self::assertStringContainsString('alert alert-info', $output);
        self::assertStringContainsString('page-title', $output);
        self::assertStringContainsString('dl-horizontal', $output);
    }
}
