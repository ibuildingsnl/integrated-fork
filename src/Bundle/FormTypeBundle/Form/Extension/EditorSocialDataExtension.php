<?php

namespace Integrated\Bundle\FormTypeBundle\Form\Extension;

use Integrated\Bundle\FormTypeBundle\EventListener\EditorSocialDataEventListener;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

class EditorSocialDataExtension extends AbstractTypeExtension
{

    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new EditorSocialDataEventListener());
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
