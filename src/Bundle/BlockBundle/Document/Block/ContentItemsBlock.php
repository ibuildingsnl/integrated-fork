<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Document\Block;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Common\Form\Mapping\Attributes as Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
#[Type\Document('Content items block')]
class ContentItemsBlock extends Block
{
    /**
     * @var ArrayCollection
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\ContentChoiceType', options: ['priority' => 450])]
    protected $items;

    /**
     * @var int
     */
    #[Assert\NotBlank]
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', options: [
        'priority' => 480,
        'choices' => [
            '1 Column' => 1,
            '2 Columns' => 2,
            '3 Columns' => 3,
            '4 Columns' => 4,
            '5 Columns' => 5,
        ],
        'attr' => [
            'style' => 'sidebar',
            'state' => 'show',
            'icon' => 'view-grid',
        ],
    ], location: 'sidebar')]
    protected $gridSize;

    /**
     * General object init.
     */
    public function __construct()
    {
        parent::__construct();

        $this->items = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items->toArray();
    }

    /**
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = new ArrayCollection($items);

        return $this;
    }

    /**
     * @return int
     */
    public function getGridSize()
    {
        return $this->gridSize;
    }

    /**
     * @param int $gridSize
     *
     * @return $this
     */
    public function setGridSize($gridSize)
    {
        $this->gridSize = $gridSize;

        return $this;
    }

    public function getType()
    {
        return 'content_items';
    }
}
