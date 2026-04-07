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
use Integrated\Bundle\BlockBundle\Document\Block\Embedded\FeaturedItemsItem;
use Integrated\Common\Form\Mapping\Attributes as Type;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
#[Type\Document('Feature item block')]
class FeaturedItemsBlock extends Block
{
    use PublishTitleTrait;

    /**
     * @var ArrayCollection
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\SortableCollectionType', options: [
        'priority' => 490,
        'entry_type' => 'Integrated\Bundle\FormTypeBundle\Form\Type\EmbeddedDocumentType',
        'entry_options' => ['data_class' => 'Integrated\Bundle\BlockBundle\Document\Block\Embedded\FeaturedItemsItem'],
        'allow_add' => true,
        'allow_delete' => true,
    ])]
    protected $items;

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
        try {
            $items = $this->items->toArray();
        } catch (\Throwable) {
            // Legacy block data can contain malformed Mongo references; skip the
            // block items instead of failing the entire page render.
            return [];
        }

        usort($items, function ($a, $b) {
            return $a->getOrder() - $b->getOrder();
        });

        return $items;
    }

    /**
     * @return $this
     */
    public function setItems(array $items = [])
    {
        $this->items = new ArrayCollection($items);

        return $this;
    }

    /**
     * @return $this
     */
    public function addItem(FeaturedItemsItem $item)
    {
        $this->items->add($item);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeItem(FeaturedItemsItem $item)
    {
        $this->items->removeElement($item);

        return $this;
    }

    public function getType()
    {
        return 'featured_items';
    }
}
