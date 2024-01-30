<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Form\DataTransformer\MaxDateTimeTransformer;
use Integrated\Common\Content\PublishTimeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class PublishTimeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDate', DateTimeType::class, [
            'placeholder' => ' ',
            'attr' => [
                'data-set-date-text' => 'Set publication date'
            ]
        ]);

        $builder->add(
            $builder->create('endDate', DateTimeType::class, [
                'placeholder' => ' ',
                'attr' => [
                    'data-set-date-text' => 'Set depublication date'
                ]
            ])->addModelTransformer(new MaxDateTimeTransformer())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => 'Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime',
            'constraints' => new Callback(function (?PublishTime $publishTime, ExecutionContextInterface $context) {
                                           if (!$publishTime) {
                                               return;
                                           }
                                           $startDate = $publishTime->getStartDate() ?: new \DateTime();
                                           $endDate = $publishTime->getEndDate();

                                           if (!$startDate instanceof \DateTime) {
                                               $publishTime->setStartDate(new \DateTime());
                                           }

                                           if (!$endDate instanceof \DateTime) {
                                               $publishTime->setEndDate(new \DateTime(PublishTimeInterface::DATE_MAX));
                                           }

                                           if ($startDate instanceof \DateTime && $endDate instanceof \DateTime) {
                                               if ($endDate < $startDate) {
                        $context->buildViolation("The end date can't be earlier than the begin date")
                                                           ->atPath('endDate')->addViolation();
                                               }
                                           }
            }),
                               ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_publish_time';
    }
}
