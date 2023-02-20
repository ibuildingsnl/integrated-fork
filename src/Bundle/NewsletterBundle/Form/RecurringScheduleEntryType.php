<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Bundle\FormTypeBundle\Form\Type\CollectionType;
use Integrated\Bundle\NewsletterBundle\Document\Schedule\RecurringScheduleEntry;
use Integrated\Bundle\NewsletterBundle\Document\Schedule\ScheduleEntryFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;

class RecurringScheduleEntryType extends AbstractType
{
    private const TITLE = 'send_every';

    private readonly ScheduleEntryFactory $entryFactory;

    public function __construct(ScheduleEntryFactory $entryFactory = null)
    {
        $this->entryFactory = $entryFactory ?: new ScheduleEntryFactory();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(self::TITLE, CollectionType::class, [
            'label' => false,
            'entry_type' => RecurringScheduleEntryPartType::class,
            'allow_add' => true,
            'add_button_text' => 'Add schedule',
            'allow_delete' => true,
            'prototype' => true,
            'prototype_data' => ['day' => 1],
            'attr' => [
                'class' => 'frequency-component',
            ],
        ]);
        $builder->addModelTransformer(new CallbackTransformer(
            fn (?RecurringScheduleEntry $entry) => [self::TITLE => $entry?->toArray() ?: []],
            fn (array $entry) => $this->entryFactory->fromArray($entry[self::TITLE]),
        ));
    }
}
