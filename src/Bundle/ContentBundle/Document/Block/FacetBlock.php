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

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Common\Form\Mapping\Attributes as Type;

/**
 * Facet block document.
 *
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
#[Type\Document('Facet block')]
class FacetBlock extends Block
{
    public const OPERATOR_OR = 'or';
    public const OPERATOR_AND = 'and';
    public const SELECTION_MODE_SINGLE = 'single';
    public const SELECTION_MODE_MULTI = 'multi';

    /**
     * @var ContentBlock
     */
    #[Type\Field(type: 'Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType', options: [
        'priority' => 500,
        'class' => 'Integrated\Bundle\ContentBundle\Document\Block\ContentBlock',
        'choice_label' => 'title',
        'placeholder' => '',
    ])]
    protected $block;

    /**
     * @var ArrayCollection
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\CollectionType', options: [
        'priority' => 490,
        'entry_type' => 'Integrated\Bundle\FormTypeBundle\Form\Type\EmbeddedDocumentType',
        'entry_options' => ['data_class' => 'Integrated\Bundle\ContentBundle\Document\Block\Embedded\FacetField'],
        'allow_add' => true,
        'allow_delete' => true,
    ])]
    protected $fields;

    /**
     * @var string
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', options: [
        'priority' => 495,
        'placeholder' => false,
        'required' => false,
        'choices' => [
            'Or' => self::OPERATOR_OR,
            'And' => self::OPERATOR_AND,
        ],
    ])]
    protected $operator = self::OPERATOR_OR;

    /**
     * @var string
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', options: [
        'priority' => 494,
        'placeholder' => false,
        'required' => false,
        'choices' => [
            'Single select' => self::SELECTION_MODE_SINGLE,
            'Multi select' => self::SELECTION_MODE_MULTI,
        ],
    ])]
    protected $selectionMode = self::SELECTION_MODE_SINGLE;

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->fields = new ArrayCollection();
    }

    /**
     * @return ContentBlock
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * @return $this
     */
    public function setBlock(ContentBlock $block)
    {
        $this->block = $block;

        return $this;
    }

    /**
     * @return Embedded\FacetField[]
     */
    public function getFields()
    {
        return $this->fields->toArray();
    }

    /**
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = new ArrayCollection($fields);

        return $this;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return $this
     */
    public function setOperator(?string $operator)
    {
        $operator = strtolower(trim((string) $operator));
        $this->operator = \in_array($operator, [self::OPERATOR_OR, self::OPERATOR_AND], true) ? $operator : self::OPERATOR_OR;

        return $this;
    }

    public function getSelectionMode(): string
    {
        return $this->selectionMode;
    }

    /**
     * @return $this
     */
    public function setSelectionMode(?string $selectionMode)
    {
        $selectionMode = strtolower(trim((string) $selectionMode));
        $this->selectionMode = \in_array($selectionMode, [self::SELECTION_MODE_SINGLE, self::SELECTION_MODE_MULTI], true) ? $selectionMode : self::SELECTION_MODE_SINGLE;

        return $this;
    }

    public function getType()
    {
        return 'facet';
    }
}
