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

use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Common\Content\ParentIDTrait;
use Integrated\Common\Content\RankableInterface;
use Integrated\Common\Content\RankTrait;
use Integrated\Common\Form\Mapping\Attributes as Type;

/**
 * Document type Taxonomy.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
#[Type\Document('Taxonomy')]
class Taxonomy extends Content implements RankableInterface
{
    use ParentIDTrait;
    use RankTrait;

    /**
     * @var string
     */
    #[Type\Field(options: ['attr' => ['style' => 'editor', 'state' => 'show']], location: 'editor')]
    protected $title;

    /**
     * @var string
     */
    #[Slug(fields: ['title'])]
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'link']], location: 'sidebar')]
    protected $slug;

    /**
     * @var string
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\EditorType', options: [
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
            'placeholder' => 'Your taxonomy description starts here',
        ],
    ], location: 'editor')]
    protected $description;

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
     * Get the description of the document.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description of the document.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getFeaturedImage(): Image|null
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(Image $featuredImage): void
    {
        $this->featuredImage = $featuredImage;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->title;
    }
}
