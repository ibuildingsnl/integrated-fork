<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\SeoMeta;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Content\Document\Storage\FileInterface;
use Integrated\Common\Content\RankableInterface;
use Integrated\Common\Content\RankTrait;
use Integrated\Common\Form\Mapping\Attributes as Type;

/**
 * Document type Article.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
#[Type\Document('Article')]
class Article extends Content implements RankableInterface
{
    use RankTrait;

    /**
     * @var string
     */
    #[Type\Field(options: [
        'priority' => 990,
        'attr' => [
            'state' => 'title_tinymce',
            'class' => 'fancy_tinymce',
            'style' => 'horizontal',
        ],
    ], location: 'editor')]
    protected $title;

    /**
     * @var string
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\EditorType', options: [
        'priority' => 980,
        'attr' => [
            'state' => 'fancy_tinymce',
            'class' => 'content-edit-form fancy_tinymce',
            'placeholder' => 'Your content starts here',
        ],
    ], location: 'editor')]
    protected $content;

    /**
     * @var string
     */
    #[Slug(fields: ['title'])]
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'link']], location: 'sidebar')]
    protected $slug;

    /**
     * @var string
     */
    #[Type\Field(options: ['attr' => ['style' => 'editor', 'state' => 'show']], location: 'editor')]
    protected $subtitle;

    /**
     * @var Image
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\MediaGalleryType', options: [
        'label' => 'Featured image',
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'media-image',
            'data-types' => '[{"type":"image","name":"Image"}]',
            'data-emptytext' => 'Select featured image',
            'data-multiple' => false,
        ],
    ], location: 'sidebar')]
    protected $featuredImage;

    /**
     * @var ArrayCollection Embedded\Author[]
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\AuthorType', options: [
        'priority' => 460,
        'label' => 'Authors',
        'attr' => ['style' => 'sidebar', 'icon' => 'user'],
    ], location: 'sidebar')]
    protected $authors;

    /**
     * @var string
     */
    #[Type\Field(options: [
        'priority' => 450,
        'attr' => ['style' => 'sidebar', 'icon' => 'megaphone'],
    ], location: 'sidebar')]
    protected $source;

    /**
     * @var string
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\UrlType', options: [
        'priority' => 440,
        'label' => 'Source URL',
        'attr' => ['style' => 'sidebar', 'icon' => 'open-new-window'],
    ], location: 'sidebar')]
    protected $sourceUrl;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\TextareaType', options: [
        'priority' => 490,
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
        ],
    ], location: 'editor')]
    protected $intro;

    /**
     * @var string
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\TextareaType', options: [
        'priority' => 420,
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'message-text',
        ],
    ], location: 'sidebar')]
    protected $description;

    /**
     * @var Embedded\Address
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\AddressType', options: [
        'priority' => 430,
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'pin-alt',
        ],
    ], location: 'sidebar')]
    protected $address;

    /**
     * @var Embedded\SeoMeta
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\SeoMetaType', options: [
        'priority' => 430,
        'attr' => [
            'label' => 'Seo Metadata',
            'style' => 'none',
            'icon' => 'pin-alt',
        ],
    ])]
    protected $seoMetadata;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->authors = new ArrayCollection();
        $this->address = new Address();
        $this->seoMetadata = new SeoMeta();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug($slug): void
    {
        $this->slug = $slug;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getFeaturedImage(): ?Image
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?Image $featuredImage): void
    {
        $this->featuredImage = $featuredImage;
    }

    public function getAuthors(): ?Collection
    {
        return $this->authors;
    }

    public function setAuthors(Collection $authors): void
    {
        $this->authors = $authors;
    }

    public function addAuthor(Embedded\Author $author): void
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
        }
    }

    public function removeAuthor(Embedded\Author $author): bool
    {
        return $this->authors->removeElement($author);
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(string $sourceUrl): void
    {
        $this->sourceUrl = $sourceUrl;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(string $intro): void
    {
        $this->intro = $intro;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function setSeoMetadata(Embedded\SeoMeta $seoMetadata): void
    {
        $this->seoMetadata = $seoMetadata;
    }

    public function getSeoMetadata(): ?Embedded\SeoMeta
    {
        return $this->seoMetadata;
    }

    public function getAddress(): ?Embedded\Address
    {
        return $this->address;
    }

    public function setAddress(Embedded\Address $address = null): void
    {
        $this->address = $address;
    }

    public function getCover(): ?StorageInterface
    {
        if ($this->getFeaturedImage() instanceof Image) {
            if ($this->getFeaturedImage()->getFile() instanceof StorageInterface) {
                return $this->getFeaturedImage()->getFile();
            }
        }

        $items = $this->getReferencesByRelationTypes(['cover', 'embedded']);
        if ($items) {
            foreach ($items as $item) {
                if ($item instanceof FileInterface) {
                    if ($item->getFile() instanceof StorageInterface) {
                        return $item->getFile();
                    }
                }
            }
        }

        return null;
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}
