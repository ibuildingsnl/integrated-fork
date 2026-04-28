<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Twig\Extension;

use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Event\PageEditorExtensionEvent;
use Integrated\Bundle\PageBundle\Twig\Extension\PageEditorExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormView;
use Twig\TwigFunction;

final class PageEditorExtensionTest extends TestCase
{
    public function testRenderEditorSidebarExtensionsDispatchesPanelsInPriorityOrder(): void
    {
        $page = new Page();
        $form = new FormView();

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            PageEditorExtensionEvent::SIDEBAR,
            function (PageEditorExtensionEvent $event) use ($page, $form): void {
                self::assertSame($page, $event->getPage());
                self::assertSame($form, $event->getForm());
                self::assertSame(['mode' => 'edit'], $event->getContext());

                $event->addPanel('<section>second</section>');
                $event->addPanel('<section>first</section>', 10);
            }
        );

        $extension = new PageEditorExtension($dispatcher);

        self::assertSame(
            "<section>first</section>\n<section>second</section>",
            $extension->renderEditorSidebarExtensions($page, $form, ['mode' => 'edit'])
        );
    }

    public function testTwigFunctionReturnsSafeHtml(): void
    {
        $extension = new PageEditorExtension(new EventDispatcher());

        $functions = $extension->getFunctions();

        self::assertCount(1, $functions);
        self::assertContainsOnlyInstancesOf(TwigFunction::class, $functions);
        self::assertSame('integrated_page_editor_sidebar_extensions', $functions[0]->getName());
        self::assertSame(['html'], $functions[0]->getSafe(new \Twig\Node\Node()));
    }
}
