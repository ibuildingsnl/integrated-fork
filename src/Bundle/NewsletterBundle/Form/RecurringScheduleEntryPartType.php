<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Bundle\NewsletterBundle\Schedule\ScheduleEntryFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;

class RecurringScheduleEntryPartType extends AbstractType
{
    private const WEEKDAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];
    private const MONTHS = [
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('frequency', ChoiceType::class, [
            'choices' => array_combine(ScheduleEntryFactory::FREQUENCIES, ScheduleEntryFactory::FREQUENCIES),
        ]);
        $builder->add('time', TimeType::class, [
            'input' => 'array',
            'widget' => 'single_text',
        ]);
        $builder->add('weekday', ChoiceType::class, [
            'choices' => array_combine(self::WEEKDAYS, self::WEEKDAYS),
        ]);
        $builder->add('day', IntegerType::class);
        $builder->add('month', ChoiceType::class, [
            'choices' => array_combine(self::MONTHS, self::MONTHS),
        ]);
        $builder->addModelTransformer(new CallbackTransformer(
            fn(?array $flat) => ($flat ?: []) + ['time' => ['hour' => $flat['hour'] ?? 0, 'minute' => $flat['minute'] ?? 0]],
            fn(array $nest) => $nest + ['hour' => $nest['time']['hour'], 'minute' => $nest['time']['minute']],
        ));
    }
}
