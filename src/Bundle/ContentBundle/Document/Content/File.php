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
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Content\Document\Storage\FileInterface;
use Integrated\Common\Form\Mapping\Attributes as Type;

/**
 * Document type File.
 *
 * @author Johnny Borg <johnny@e-active.nl>
 */
#[Type\Document('File')]
class File extends Content implements FileInterface
{
    /**
     * @var string
     */
    #[Slug(fields: ['title'])]
    #[Type\Field(options: [
        'attr' => [
            'style' => 'sidebar',
            'state' => 'show',
            'icon' => 'link',
        ],
    ], location: 'sidebar')]
    protected $slug;

    /**
     * @var StorageInterface
     */
    #[Type\Field(type: 'Integrated\Bundle\StorageBundle\Form\Type\FileDropzoneType', options: [
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
        ],
    ], location: 'editor')]
    protected $file;

    /**
     * @var StorageInterface
     */
    #[Type\Field(type: 'Integrated\Bundle\StorageBundle\Form\Type\FileDropzoneType', options: [
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
        ],
    ], location: 'editor')]
    protected $editedFile;

    /**
     * @var string
     */
    #[Type\Field(options: [
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
        ],
    ], location: 'editor')]
    protected $title;

    /**
     * @var string
     */
    #[Type\Field(options: [
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
        ],
    ], location: 'editor')]
    protected $description;

    /**
     * @var string
     */
    #[Type\Field(options: [
        'attr' => [
            'style' => 'sidebar',
            'state' => 'show',
            'icon' => 'copyright',
        ],
    ], location: 'sidebar')]
    protected $credits;

    /**
     * {@inheritdoc}
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function setFile(StorageInterface $file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
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
     * @return ?string
     */
    public function getCredits(): ?string
    {
        return $this->credits;
    }

    /**
     * @param string $credits
     */
    public function setCredits(?string $credits): self
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->title;
    }
}
