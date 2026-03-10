<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Twig\Component\Admin;

use Integrated\Bundle\ContentBundle\Twig\Component\Admin\AsidePanel;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\DataTable;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\EditDrawerPanel;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\FilterGroup;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\FolderMenuPanel;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\OptionsToolbar;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\PaginationFooter;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\PageTitle;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\SectionCard;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\StatusBadge;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;
use Symfony\UX\TwigComponent\TwigComponentBundle;

final class AdminComponentRenderingTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new AdminComponentRenderingTestKernel('test', true);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
        parent::tearDown();
    }

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
    public function itRendersPageTitleMarkupWithNestedContentHtml(): void
    {
        $output = $this->renderTwigComponent('integrated_admin:page_title', [
            'title' => 'Mail accounts',
            'contentHtml' => '<div class="options options-toolbar"><ul class="options-no-bg"><li>Toolbar</li></ul></div>',
        ])->toString();

        self::assertStringContainsString('page-title', $output);
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
            'listItems' => [
                ['label' => 'Overview', 'href' => '/admin/content'],
                ['label' => 'Settings', 'href' => '/admin/settings', 'active' => true],
            ],
        ])->toString();

        self::assertStringContainsString('options options-toolbar', $output);
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
        ])->toString();

        self::assertStringContainsString('<table', $output);
        self::assertStringContainsString('table table-hover', $output);
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
}

final class AdminComponentRenderingTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new TwigComponentBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'integrated-admin-component-render-tests',
            'test' => true,
            'router' => ['utf8' => true],
            'http_method_override' => false,
        ]);

        $container->extension('twig', [
            'strict_variables' => true,
            'paths' => [
                self::projectDir().'/vendor/integrated/integrated/src/Bundle/ContentBundle/Resources/views' => 'IntegratedContent',
            ],
        ]);

        $services = $container->services()->defaults()->autowire(true)->autoconfigure(true)->public();

        $services->set(StatusBadge::class);
        $services->set(SectionCard::class);
        $services->set(PageTitle::class);
        $services->set(OptionsToolbar::class);
        $services->set(DataTable::class);
        $services->set(AsidePanel::class)->tag('twig.component');
        $services->set(EditDrawerPanel::class)->tag('twig.component');
        $services->set(FilterGroup::class)->tag('twig.component');
        $services->set(FolderMenuPanel::class)->tag('twig.component');
        $services->set(PaginationFooter::class)->tag('twig.component');
        $services->set(PaginationFooterTestTwigExtension::class)->tag('twig.extension');
    }

    public function getCacheDir(): string
    {
        $suffix = md5((string) @filemtime(__FILE__));

        return sys_get_temp_dir().'/integrated_content_bundle_component_tests/'.$suffix.'/cache';
    }

    public function getLogDir(): string
    {
        $suffix = md5((string) @filemtime(__FILE__));

        return sys_get_temp_dir().'/integrated_content_bundle_component_tests/'.$suffix.'/logs';
    }

    public function getProjectDir(): string
    {
        return self::projectDir();
    }

    private static function projectDir(): string
    {
        return \dirname(__DIR__, 10);
    }
}

final class PaginationFooterTestPager implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {
        yield 1;
        yield 2;
    }
}

final class PaginationFooterTestTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'knp_pagination_render',
                static function (mixed $pagination, string $template): string {
                    return sprintf(
                        '<nav class="pagination-test" data-template="%s"><a class="pagination-page-1">1</a><a class="pagination-page-2">2</a></nav>',
                        htmlspecialchars($template, ENT_QUOTES)
                    );
                },
                ['is_safe' => ['html']]
            ),
        ];
    }
}
