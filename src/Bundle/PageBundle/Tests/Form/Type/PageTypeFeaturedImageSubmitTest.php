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
use Integrated\Bundle\PageBundle\Form\Type\LayoutChoiceType;
use Integrated\Bundle\PageBundle\Form\Type\PageType;
use Integrated\Bundle\PageBundle\Locator\LayoutLocator;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

final class PageTypeFeaturedImageSubmitTest extends TypeTestCase
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

    public function testSubmitBindsFeaturedImageToPage(): void
    {
        $page = new Page();
        $page->setSeoMetadata(new SeoMeta());

        $form = $this->factory->create(PageType::class, $page);
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
            'featuredImage' => 'image-1',
            'robotsDirective' => 'default',
            'twitterCard' => 'default',
            'path' => 'home',
            'disabled' => false,
            'paginationNoindexEnabled' => true,
            'layout' => 'default',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertInstanceOf(Image::class, $page->getFeaturedImage());
    }
}
