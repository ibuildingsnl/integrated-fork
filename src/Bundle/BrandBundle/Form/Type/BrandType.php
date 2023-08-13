<?php

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\FormTypeBundle\Form\Type\SortableCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BrandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('profile', BrandProfileType::class, ['data_class' => BrandProfile::class]);
        $builder->add('channelLinks', SortableCollectionType::class, [
            'entry_type' => ChannelLinkType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'add_button_text' => 'Channels',
            'label' => 'Channels',
            'attr' => ['location' => 'sidebar', 'style' => 'sidebar', 'state' => 'show', 'show_headings' => false],
        ]);
    }
}
