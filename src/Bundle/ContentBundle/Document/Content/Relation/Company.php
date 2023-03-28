<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content\Relation;

use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Content\Document\Storage\FileInterface;
use Integrated\Common\Form\Mapping\Attributes as Type;

/**
 * Document type Relation\Company.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
#[Type\Document('Company')]
class Company extends Relation
{
    /**
     * @var string
     */
    #[Type\Field(options: [
        'attr' => [
            'state' => 'title_tinymce',
            'class' => 'fancy_tinymce',
            'style' => 'horizontal',
        ],
    ], location: 'editor')]
    protected $name;

    /**
     * @var string
     */
    #[Slug(fields: ['name'])]
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'link']], location: 'sidebar')]
    protected $slug;

    /**
     * @var Image
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\MediaGalleryType', options: [
        'priority' => 500,
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'media-image',
            'data-types' => '[{"type":"image","name":"Image"}]',
            'data-emptytext' => 'Select Logo',
            'data-multiple' => false,
        ],
    ], location: 'sidebar')]
    protected $logo;

    /**
     * @var string
     */
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'www']], location: 'sidebar')]
    protected $website;


    public function getTitle(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug($slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLogo(): ?Image
    {
        return $this->logo;
    }

    public function setLogo(Image $logo = null): static
    {
        $this->logo = $logo;

        return $this;
    }


    public function getWebsite(): string
    {
        return $this->website;
    }

    public function setWebsite(string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getCover(): ?string
    {
        if ($this->getLogo() instanceof Image) {
            if ($this->getLogo()->getFile() instanceof StorageInterface) {
                return $this->getLogo()->getFile();
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
        return (string) $this->name;
    }
}
