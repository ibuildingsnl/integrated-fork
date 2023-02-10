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
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Common\Form\Mapping\Attributes as Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form block document.
 *
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
#[Type\Document('Form block')]
class FormBlock extends Block
{
    use PublishTitleTrait;

    #[Type\Field(type: 'Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType', options: [
        'class' => 'Integrated\Bundle\ContentBundle\Document\ContentType\ContentType',
        'choice_label' => 'name',
        'placeholder' => '',
    ])]
    protected ContentType $contentType;

    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\EditorType', options: ['mode' => 'web'])]
    protected string $content;

    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\TextType', options: ['required' => false])]
    protected string $returnUrl;

    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\TextareaType', options: ['required' => false])]
    protected string $textAfterSubmit;

    /**
     * @Assert\All({
     *     @Assert\Email
     * })
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\TailwindCollectionType', options: [
        'label' => 'Sent form to e-mail address(es)',
        'entry_type' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
        'allow_add' => true,
        'allow_delete' => true,
        'required' => false,
    ])]
    protected array $emailAddresses = [];

    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType', options: [
        'label' => 'Enable reCaptcha for this form',
        'required' => false,
        'attr' => ['align_with_widget' => true],
    ])]
    protected bool $recaptcha = false;

    #[Type\Field(type: 'Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType', options: [
        'label' => 'Link to content item',
        'class' => 'Integrated\Bundle\ContentBundle\Document\Relation\Relation',
        'choice_label' => 'name',
        'placeholder' => 'Do not link',
        'required' => false,
    ])]
    protected Relation $linkRelation;

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function setContentType(ContentType $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    public function setReturnUrl(string $returnUrl): self
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    public function getTextAfterSubmit(): string
    {
        return $this->textAfterSubmit;
    }

    public function setTextAfterSubmit(string $textAfterSubmit): self
    {
        $this->textAfterSubmit = $textAfterSubmit;

        return $this;
    }

    public function getEmailAddresses(): array
    {
        return $this->emailAddresses;
    }

    public function setEmailAddresses(array $emailAddresses = []): self
    {
        $this->emailAddresses = $emailAddresses;

        return $this;
    }

    public function isRecaptcha(): bool
    {
        return $this->recaptcha;
    }

    public function setRecaptcha(bool $recaptcha): self
    {
        $this->recaptcha = $recaptcha;

        return $this;
    }

    public function getLinkRelation(): Relation
    {
        return $this->linkRelation;
    }

    public function setLinkRelation(Relation $linkRelation): self
    {
        $this->linkRelation = $linkRelation;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'form';
    }
}
