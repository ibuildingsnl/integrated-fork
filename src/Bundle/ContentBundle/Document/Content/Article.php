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
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\MediaGalleryImageType', options: [
        'label' => 'Featured Image',
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
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'megaphone']], location: 'sidebar')]
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
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->authors = new ArrayCollection();
        $this->address = new Address();
    }

    /**
     * Get the title of the document.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title of the document.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the content of the document.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the content of the document.
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the slug of the document.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the slug of the document.
     *
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get the subtitle of the document.
     *
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set the subtitle of the document.
     *
     * @param string $subtitle
     *
     * @return $this
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getFeaturedImage(): StorageInterface|null
    {
        return $this->featuredImage->getFile();
    }

    public function setFeaturedImage(Image $featuredImage): void
    {
        $this->featuredImage = $featuredImage;
    }

    /**
     * Get the authors of the document.
     *
     * @return Collection
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Set the authors of the document.
     *
     * @return $this
     */
    public function setAuthors(Collection $authors)
    {
        $this->authors = $authors;

        return $this;
    }

    /**
     * Add author to authors collection.
     *
     * @return $this
     */
    public function addAuthor(Embedded\Author $author)
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
        }

        return $this;
    }

    /**
     * @return bool true if this collection contained the specified element, false otherwise
     */
    public function removeAuthor(Embedded\Author $author)
    {
        return $this->authors->removeElement($author);
    }

    /**
     * Get the source of the document.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set the source of the document.
     *
     * @param string $source
     *
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get the source URL of the document.
     *
     * @return string
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    /**
     * Set the source of the document.
     *
     * @param string $sourceUrl
     *
     * @return $this
     */
    public function setSourceUrl($sourceUrl)
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    /**
     * Get the locale of the document.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the locale of the document.
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the intro of the document.
     *
     * @return string
     */
    public function getIntro()
    {
        return $this->intro;
    }

    /**
     * Set the intro of the document.
     *
     * @param string $intro
     *
     * @return $this
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the address of the document.
     *
     * @return Embedded\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the address of the document.
     *
     * @param Embedded\Address $address
     *
     * @return $this
     */
    public function setAddress(Embedded\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get the relative cover image URL for article.
     *
     * @return StorageInterface
     */
    public function getCover()
    {
        if ($this->getFeaturedImage() instanceof FileInterface) {
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

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->title;
    }
}
