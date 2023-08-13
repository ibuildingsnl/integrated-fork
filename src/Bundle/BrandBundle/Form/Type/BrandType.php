<?php

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\FormTypeBundle\Form\Type\SortableCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $types = array_column($event->getData()['channelLinks'] ?? [], 'type');
            if ($types !== array_unique($types)) {
                $event->getForm()->get('channelLinks')->addError(new FormError('Select unique channel types'));
            }
        });
    }
}
