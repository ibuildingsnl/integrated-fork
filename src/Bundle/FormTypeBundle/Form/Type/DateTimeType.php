<?php

namespace Integrated\Bundle\FormTypeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DateTimeType extends AbstractType
{
    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_datetime';
    }
}
