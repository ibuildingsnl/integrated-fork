<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Bundle\NewsletterBundle\Document\Schedule\ScheduleEntryFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;

class RecurringScheduleEntryPartType extends AbstractType
{
    private const WEEKDAYS = [
        'Monday' => 'monday',
        'Tuesday' => 'tuesday',
        'Wednesday' => 'wednesday',
        'Thursday' => 'thursday',
        'Friday' => 'friday',
        'Saturday' => 'saturday',
        'Sunday' => 'sunday',
    ];
    private const MONTHS = [
        'January' => 'january',
        'February' => 'february',
        'March' => 'march',
        'April' => 'april',
        'May' => 'may',
        'June' => 'june',
        'July' => 'july',
        'August' => 'august',
        'September' => 'september',
        'October' => 'october',
        'November' => 'november',
        'December' => 'december',
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
        $builder->add('day', IntegerType::class, [
            'empty_data' => 1,
            'attr' => [
                'data-if-frequency' => 'monthly|quarterly|yearly',
            ],
        ]);
        $builder->add('weekday', ChoiceType::class, [
            'choices' => self::WEEKDAYS,
            'attr' => [
                'data-if-frequency' => 'weekly|monthly',
            ],
        ]);
        $builder->add('month', ChoiceType::class, [
            'choices' => self::MONTHS,
            'attr' => [
                'data-if-frequency' => 'yearly',
            ],
        ]);
        $builder->addModelTransformer(new CallbackTransformer(
            fn (?array $flat) => ($flat ?: []) + ['time' => ['hour' => $flat['hour'] ?? 0, 'minute' => $flat['minute'] ?? 0]],
            fn (array $nest) => $nest + ['hour' => $nest['time']['hour'], 'minute' => $nest['time']['minute']],
        ));
    }
}
