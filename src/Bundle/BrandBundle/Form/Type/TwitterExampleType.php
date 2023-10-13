<?php

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Form\Type\PublishTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

// @todo move to proper bundle
// @todo make nice
class TwitterExampleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('time', PublishTimeType::class, ['label' => false]);
        $builder->add('text', TextareaType::class);
    }
}
