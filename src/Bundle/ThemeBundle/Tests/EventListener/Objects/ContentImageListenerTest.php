<?php

declare(strict_types=1);

namespace Integrated\Bundle\ThemeBundle\Tests\EventListener\Objects;

use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Event\ContentRenderEvent;
use Integrated\Bundle\SlugBundle\Slugger\SluggerInterface;
use Integrated\Bundle\ThemeBundle\EventListener\Objects\ContentImageListener;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

final class ContentImageListenerTest extends TestCase
{
    public function testReplaceImagesMarksFirstInlineImageAsEagerAndLaterImagesAsLazy(): void
    {
        $themeManager = $this->createMock(ThemeManager::class);
        $objectManager = $this->createMock(ObjectManager::class);
        $templating = $this->createMock(Environment::class);
        $slugger = $this->createMock(SluggerInterface::class);
        $document = $this->createMock(Content::class);

        $themeManager
            ->method('locateTemplate')
            ->with('objects/image/default.html.twig')
            ->willReturn('@Theme/objects/image/default.html.twig');

        $objectManager
            ->expects(self::exactly(2))
            ->method('find')
            ->with(Content::class)
            ->willReturn($document);

        $renderCalls = [];

        $templating
            ->expects(self::exactly(2))
            ->method('render')
            ->with(
                '@Theme/objects/image/default.html.twig',
                self::callback(function (array $context) use (&$renderCalls, $document): bool {
                    $renderCalls[] = $context;

                    return $context['document'] === $document;
                })
            )
            ->willReturnCallback(static fn (string $template, array $context): string => sprintf(
                '<img data-loading="%s" data-fetchpriority="%s" data-decoding="%s">',
                $context['loading'],
                $context['fetchPriority'],
                $context['decoding']
            ));

        $listener = new ContentImageListener(
            $themeManager,
            $objectManager,
            $templating,
            $slugger,
            'test'
        );

        $event = new ContentRenderEvent(
            '<p><img data-integrated-id="first"></p><p><img data-integrated-id="second"></p>'
        );

        $listener->replaceImages($event);

        self::assertCount(2, $renderCalls);
        self::assertSame('eager', $renderCalls[0]['loading']);
        self::assertSame('high', $renderCalls[0]['fetchPriority']);
        self::assertSame('async', $renderCalls[0]['decoding']);
        self::assertSame('lazy', $renderCalls[1]['loading']);
        self::assertSame('low', $renderCalls[1]['fetchPriority']);
        self::assertSame('async', $renderCalls[1]['decoding']);
        self::assertStringContainsString('data-loading="eager"', $event->getContent());
        self::assertStringContainsString('data-loading="lazy"', $event->getContent());
    }
}
