<?php

declare(strict_types=1);

namespace Integrated\Bundle\ThemeBundle\Tests\EventListener\Objects;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
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
        $documentManager = $this->createMock(DocumentManager::class);
        $repository = $this->createMock(ObjectRepository::class);
        $templating = $this->createMock(Environment::class);
        $slugger = $this->createMock(SluggerInterface::class);
        $firstDocument = $this->createMock(Content::class);
        $secondDocument = $this->createMock(Content::class);

        $firstDocument
            ->method('getId')
            ->willReturn('first');

        $secondDocument
            ->method('getId')
            ->willReturn('second');

        $themeManager
            ->method('locateTemplate')
            ->with('objects/image/default.html.twig')
            ->willReturn('@Theme/objects/image/default.html.twig');

        $documentManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(Content::class)
            ->willReturn($repository);

        $repository
            ->expects(self::once())
            ->method('findBy')
            ->with(['_id' => ['$in' => ['first', 'second']]])
            ->willReturn([$firstDocument, $secondDocument]);

        $renderCalls = [];

        $templating
            ->expects(self::exactly(2))
            ->method('render')
            ->with(
                '@Theme/objects/image/default.html.twig',
                self::callback(function (array $context) use (&$renderCalls, $firstDocument, $secondDocument): bool {
                    $renderCalls[] = $context;

                    return \in_array($context['document'], [$firstDocument, $secondDocument], true);
                })
            )
            ->willReturnCallback(static fn (string $template, array $context): string => \sprintf(
                '<img data-loading="%s" data-fetchpriority="%s" data-decoding="%s">',
                $context['loading'],
                $context['fetchPriority'],
                $context['decoding']
            ));

        $listener = new ContentImageListener(
            $themeManager,
            $documentManager,
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
