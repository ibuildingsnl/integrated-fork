<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Twig\Extension;

use Integrated\Bundle\ContentBundle\Event\ContentEditorExtensionEvent;
use Integrated\Bundle\ContentBundle\Twig\Extension\ContentEditorExtension;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\ContentType\ContentTypeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class ContentEditorExtensionTest extends TestCase
{
    public function testRenderEditorSidebarExtensionsDispatchesPanelsInPriorityOrder(): void
    {
        $content = $this->createMock(ContentInterface::class);
        $contentType = $this->createMock(ContentTypeInterface::class);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            ContentEditorExtensionEvent::SIDEBAR,
            function (ContentEditorExtensionEvent $event) use ($content, $contentType): void {
                self::assertSame($content, $event->getContent());
                self::assertSame($contentType, $event->getContentType());
                self::assertSame(['mode' => 'edit'], $event->getContext());

                $event->addPanel('<section>second</section>');
                $event->addPanel('<section>first</section>', 10);
            }
        );

        $extension = new ContentEditorExtension($dispatcher);

        self::assertSame(
            "<section>first</section>\n<section>second</section>",
            $extension->renderEditorSidebarExtensions($content, $contentType, ['mode' => 'edit'])
        );
    }
}
