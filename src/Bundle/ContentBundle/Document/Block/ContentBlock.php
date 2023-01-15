<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Block;

use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Document\Block\PublishTitleTrait;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Common\Form\Mapping\Attributes as Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Content block document.
 *
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
#[Type\Document('Content block')]
class ContentBlock extends Block
{
    use PublishTitleTrait;

    /**
     * @var SearchSelection
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\SearchSelectionChoiceType')]
    protected $searchSelection;

    /**
     * @var int
     */
    #[Assert\Length(min: 0)]
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\IntegerType', options: ['attr' => ['min' => 0, 'label' => 'Items per page']])]
    protected $itemsPerPage = 10;

    /**
     * @var int
     */
    #[Assert\Length(min: 0)]
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\IntegerType', options: ['required' => false, 'attr' => ['min' => 0, 'label' => 'Max items'], ])]
    protected $maxItems;

    /**
     * @var int
     */
    #[Assert\NotBlank]
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', options: [
        'choices' => [
            '1 Column' => 1,
            '2 Columns' => 2,
            '3 Columns' => 3,
            '4 Columns' => 4,
            '5 Columns' => 5,
        ],
    ]
    )]
    protected $gridSize;

    /**
     * @var string
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\TextType', options: ['required' => false])]
    protected $readMoreUrl;

    /**
     * @var array
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\TailwindCollectionType', options: ['allow_add' => true,
        'add_button_text' => 'Add Facet field', 'allow_delete' => true, 'required' => false, 'attr' => ['show_headings' => 'false']])]
    protected $facetFields = [];

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Type\Field(options: ['attr' => ['class' => 'main-title']])]
    protected $title;

    /**
     * @return SearchSelection
     */
    public function getSearchSelection()
    {
        return $this->searchSelection;
    }

    /**
     * @param SearchSelection $searchSelection
     *
     * @return $this
     */
    public function setSearchSelection(SearchSelection $searchSelection = null)
    {
        $this->searchSelection = $searchSelection;

        return $this;
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $itemsPerPage
     *
     * @return $this
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxItems()
    {
        return $this->maxItems;
    }

    /**
     * @param int $maxItems
     *
     * @return $this
     */
    public function setMaxItems($maxItems)
    {
        $this->maxItems = $maxItems;

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

    /**
     * @return string
     */
    public function getReadMoreUrl()
    {
        return $this->readMoreUrl;
    }

    /**
     * @param string $readMoreUrl
     *
     * @return $this
     */
    public function setReadMoreUrl($readMoreUrl)
    {
        $this->readMoreUrl = $readMoreUrl;

        return $this;
    }

    /**
     * @return array
     */
    public function getFacetFields()
    {
        return $this->facetFields;
    }

    /**
     * @return $this
     */
    public function setFacetFields(array $facetFields = [])
    {
        $this->facetFields = $facetFields;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'content';
    }
}
