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

    /**
     * @var ContentType
     */
    #[Type\Field(type: 'Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType', options: [
        'priority' => 500,
        'class' => 'Integrated\Bundle\ContentBundle\Document\ContentType\ContentType',
        'choice_label' => 'name',
        'placeholder' => '',
    ])]
    protected $contentType;

    /**
     * @var string
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\EditorType', options: [
        'priority' => 480,
        'mode' => 'web'])]
    protected $content;

    /**
     * @var string
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\TextType', options: [
        'priority' => 470,
        'required' => false])]
    protected $returnUrl;

    /**
     * @var string
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\TextareaType', options: [
        'priority' => 460,
        'required' => false])]
    protected $textAfterSubmit;

    /**
     * @var array
     *
     * @Assert\All({
     *     @Assert\Email
     * })
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\TailwindCollectionType', options: [
        'priority' => 450,
        'label' => 'Sent form to e-mail address(es)',
        'entry_type' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
        'allow_add' => true,
        'allow_delete' => true,
        'required' => false,
    ])]
    protected $emailAddresses = [];

    /**
     * @var bool
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType', options: [
        'priority' => 440,
        'label' => 'Enable reCaptcha for this form',
        'required' => false,
        'attr' => ['align_with_widget' => true],
    ])]
    protected $recaptcha = false;

    /**
     * @var Relation
     */
    #[Type\Field(type: 'Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType', options: [
        'priority' => 490,
        'label' => 'Link to content item',
        'class' => 'Integrated\Bundle\ContentBundle\Document\Relation\Relation',
        'choice_label' => 'name',
        'placeholder' => 'Do not link',
        'required' => false,
    ])]
    protected $linkRelation;

    /**
     * @return ContentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return $this
     */
    public function setContentType(ContentType $contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
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
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     *
     * @return $this
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getTextAfterSubmit()
    {
        return $this->textAfterSubmit;
    }

    /**
     * @param string $textAfterSubmit
     *
     * @return $this
     */
    public function setTextAfterSubmit($textAfterSubmit)
    {
        $this->textAfterSubmit = $textAfterSubmit;

        return $this;
    }

    /**
     * @return array
     */
    public function getEmailAddresses()
    {
        return $this->emailAddresses;
    }

    /**
     * @return $this
     */
    public function setEmailAddresses(array $emailAddresses = [])
    {
        $this->emailAddresses = $emailAddresses;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRecaptcha()
    {
        return $this->recaptcha;
    }

    /**
     * @param bool $recaptcha
     *
     * @return $this
     */
    public function setRecaptcha($recaptcha)
    {
        $this->recaptcha = $recaptcha;

        return $this;
    }

    /**
     * @return Relation
     */
    public function getLinkRelation()
    {
        return $this->linkRelation;
    }

    /**
     * @param Relation $linkRelation
     *
     * @return $this
     */
    public function setLinkRelation($linkRelation)
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
