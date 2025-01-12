<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DefaultPublicationSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('time', PublishTimeType::class, ['label' => false]);
    }
}
