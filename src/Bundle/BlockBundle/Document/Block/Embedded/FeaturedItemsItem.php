<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Document\Block\Embedded;

use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Form\Type\MediaGalleryType;
use Integrated\Common\Form\Mapping\Attributes as Type;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
#[Type\Document('FeaturedItemsItem')]
class FeaturedItemsItem
{
    /**
     * @var string
     */
    #[Type\Field]
    protected $title;

    #[Type\Field(type: MediaGalleryType::class, options: [
        'priority' => 500,
        'attr' => [
            'icon' => 'media-image',
            'data-types' => '[{"type":"image","name":"Image"}]',
            'data-emptytext' => 'Select Image',
            'data-multiple' => false,
        ],
    ], location: 'sidebar')]
    protected ?Image $image = null;

    /**
     * @var string
     */
    #[Type\Field(options: ['required' => false])]
    protected $link;

    /**
     * @var string
     */
    #[Type\Field(options: ['required' => false])]
    protected $linkText;

    /**
     * @var string
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', options: ['label' => 'Link target', 'expanded' => true, 'choices' => ['Current window' => '_self', 'New window' => '_blank']])]
    protected $target = '_self';

    /**
     * @var string
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\EditorType', options: [
        'label' => 'Content',
        'mode' => 'web',
        'attr' => ['align_with_widget' => true],
    ])]
    protected $text;

    /**
     * @var bool
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType', options: ['required' => false, 'attr' => ['align_with_widget' => true]])]
    protected $disabled = false;

    /**
     * @var int
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\HiddenType', options: ['attr' => ['data-itemorder' => 'collection']])]
    protected $order = 0;

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

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image = null): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getLinkText()
    {
        return $this->linkText;
    }

    /**
     * @param string $linkText
     *
     * @return $this
     */
    public function setLinkText($linkText)
    {
        $this->linkText = $linkText;

        return $this;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     *
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     *
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = (int) $order;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}
