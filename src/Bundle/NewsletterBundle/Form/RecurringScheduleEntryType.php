<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Bundle\NewsletterBundle\Form\RecurringScheduleEntryPartType;
use Integrated\Bundle\FormTypeBundle\Form\Type\CollectionType;
use Integrated\Bundle\NewsletterBundle\Schedule\RecurringScheduleEntry;
use Integrated\Bundle\NewsletterBundle\Schedule\ScheduleEntryFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;

class RecurringScheduleEntryType extends AbstractType
{
    private readonly ScheduleEntryFactory $entryFactory;

    public function __construct(ScheduleEntryFactory $entryFactory = null)
    {
        $this->entryFactory = $entryFactory ?: new ScheduleEntryFactory();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('send', CollectionType::class, [
            'entry_type' => RecurringScheduleEntryPartType::class,
            "allow_add" => true,
            "allow_delete" => true,
            'prototype' => true,
        ]);
        $builder->addModelTransformer(new CallbackTransformer(
            fn(?RecurringScheduleEntry $entry) => ['parts' => $entry?->toArray() ?: []],
            fn(array $entry) => $this->entryFactory->fromArray($entry['parts']),
        ));
    }
}
