<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Form\Type;

use Doctrine\Bundle\MongoDBBundle\Form\DoctrineMongoDBExtension;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\SeoMeta;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType;
use Integrated\Bundle\ContentBundle\Form\Type\MediaGalleryType;
use Integrated\Bundle\ContentBundle\Form\Type\SeoMetaType;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Locator\LayoutLocator;
use Integrated\Bundle\PageBundle\Form\Type\LayoutChoiceType;
use Integrated\Bundle\PageBundle\Form\Type\PageType;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

final class PageTypeLifecycleFieldsTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']);
        $metadata->method('getTypeOfField')->willReturn('string');
        $metadata->method('hasAssociation')->willReturn(false);
        $metadata->method('getIdentifierValues')->willReturn(['id' => 'image-1']);

        $image = new Image();
        $image->setTitle('Featured image');

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->method('find')
            ->with('image-1')
            ->willReturn($image);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getClassMetadata')
            ->willReturn($metadata);
        $documentManager
            ->method('getRepository')
            ->with(Image::class)
            ->willReturn($repository);
        $documentManager
            ->method('contains')
            ->willReturn(true);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->method('getManagerForClass')
            ->willReturn($documentManager);

        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext
            ->method('getChannel')
            ->willReturn(null);

        $channelRepository = $this->createMock(ObjectRepository::class);
        $channelRepository
            ->method('findBy')
            ->willReturn([]);

        $themeResolver = $this->createMock(ThemeResolver::class);
        $themeResolver
            ->method('getTheme')
            ->willReturn('default');

        $layoutLocator = $this->createMock(LayoutLocator::class);
        $layoutLocator
            ->method('getLayouts')
            ->willReturn(['default']);

        return [
            new DoctrineMongoDBExtension($registry),
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension([
                new PageType($channelContext, $themeResolver, true),
                new MediaGalleryType($documentManager),
                new CheckboxSwitcherType(),
                new SeoMetaType(),
                new LayoutChoiceType($layoutLocator),
                new ChannelChoiceType($channelRepository),
            ], []),
        ];
    }

    public function testPageTypeExposesLifecycleFields(): void
    {
        $form = $this->createPageForm();

        self::assertTrue($form->has('publishAt'));
        self::assertTrue($form->has('expireAt'));
        self::assertTrue($form->has('expireRedirectUrl'));
        self::assertTrue($form->has('hideFromSitemap'));
    }

    public function testSubmitAcceptsEmptyLifecycleValues(): void
    {
        $page = $this->createPage();
        $form = $this->createPageForm($page);

        $form->submit([
            'channel' => '',
            'title' => 'Home',
            'description' => 'Description',
            'seoMetadata' => [
                'metaTitle' => 'Meta title',
                'metaDescription' => 'Meta description',
                'focusKeyphrase' => 'focus',
                'seoScore' => 'good',
                'readabilityScore' => 'ok',
            ],
            'canonicalUrl' => '',
            'featuredImage' => '',
            'robotsDirective' => 'default',
            'twitterCard' => 'default',
            'publishAt' => ['date' => '', 'time' => ''],
            'expireAt' => ['date' => '', 'time' => ''],
            'expireRedirectUrl' => '',
            'hideFromSitemap' => false,
            'path' => 'home',
            'disabled' => false,
            'paginationNoindexEnabled' => true,
            'layout' => 'default',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertNull($page->getPublishAt());
        self::assertNull($page->getExpireAt());
        self::assertNull($page->getExpireRedirectUrl());
        self::assertFalse($page->isHideFromSitemap());
    }

    /**
     * @dataProvider validExpireRedirectUrlProvider
     */
    public function testSubmitAcceptsValidExpireRedirectUrls(string $redirectUrl): void
    {
        $page = $this->createPage();
        $form = $this->createPageForm($page);

        $form->submit($this->baseSubmitData([
            'expireRedirectUrl' => $redirectUrl,
        ]));

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertSame($redirectUrl, $page->getExpireRedirectUrl());
    }

    /**
     * @dataProvider invalidExpireRedirectUrlProvider
     */
    public function testSubmitRejectsInvalidExpireRedirectUrls(string $redirectUrl): void
    {
        $page = $this->createPage();
        $form = $this->createPageForm($page);

        $form->submit($this->baseSubmitData([
            'expireRedirectUrl' => $redirectUrl,
        ]));

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
    }

    public function testSubmitRejectsExpireAtBeforePublishAt(): void
    {
        $page = $this->createPage();
        $form = $this->createPageForm($page);

        $form->submit($this->baseSubmitData([
            'publishAt' => ['date' => '2026-03-19', 'time' => '10:00'],
            'expireAt' => ['date' => '2026-03-18', 'time' => '10:00'],
        ]));

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
    }

    public static function validExpireRedirectUrlProvider(): iterable
    {
        yield 'absolute URL' => ['https://example.com/expired'];
        yield 'relative path' => ['/expired'];
    }

    public static function invalidExpireRedirectUrlProvider(): iterable
    {
        yield 'missing leading slash and scheme' => ['expired'];
    }

    private function createPage(): Page
    {
        $page = new Page();
        $page->setSeoMetadata(new SeoMeta());

        return $page;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function baseSubmitData(array $overrides = []): array
    {
        return array_merge([
            'channel' => '',
            'title' => 'Home',
            'description' => 'Description',
            'seoMetadata' => [
                'metaTitle' => 'Meta title',
                'metaDescription' => 'Meta description',
                'focusKeyphrase' => 'focus',
                'seoScore' => 'good',
                'readabilityScore' => 'ok',
            ],
            'canonicalUrl' => '',
            'featuredImage' => '',
            'robotsDirective' => 'default',
            'twitterCard' => 'default',
            'publishAt' => ['date' => '', 'time' => ''],
            'expireAt' => ['date' => '', 'time' => ''],
            'expireRedirectUrl' => '',
            'hideFromSitemap' => false,
            'path' => 'home',
            'disabled' => false,
            'paginationNoindexEnabled' => true,
            'layout' => 'default',
        ], $overrides);
    }

    private function createPageForm(?Page $page = null): FormInterface
    {
        return $this->factory->create(PageType::class, $page ?? $this->createPage());
    }
}
