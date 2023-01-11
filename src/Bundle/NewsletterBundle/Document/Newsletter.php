<?php

namespace Integrated\Bundle\NewsletterBundle\Document;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\NewsletterBundle\Form\RecurringScheduleEntryType;
use Integrated\Bundle\FormTypeBundle\Form\Type\CollectionType;
use Integrated\Bundle\NewsletterBundle\Schedule\RecurringScheduleEntry;
use Integrated\Common\Form\Mapping\Attributes as Type;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

#[Type\Document('Newsletter')]
class Newsletter extends Content
{
    #[Type\Field]
    public string $title = 'untitled';
    /** @var string[] */
    #[Type\Field(type: CollectionType::class, options: [
        'entry_type' => EmailType::class,
        'entry_options' => [
            'attr' => [
                'placeholder' => 'Send testmail to...',
                'label' => 'Send testmail to...',
            ],
        ],
        'allow_add' => true,
        'allow_delete' => true,
        'prototype' => true,
    ])]
    public array $testAddresses = [];
    #[Type\Field(type: RecurringScheduleEntryType::class)]
    public RecurringScheduleEntry $schedule;

    public function __toString()
    {
        return $this->title;
    }
}
