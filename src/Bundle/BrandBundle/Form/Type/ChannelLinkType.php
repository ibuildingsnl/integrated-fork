<?php

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Document\LinkType;
use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Form\Type\ChannelType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChannelLinkType extends AbstractType
{
    /** @param iterable<LinkType> $linkTypes */
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly iterable $linkTypes,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', ChoiceType::class, [
            'choices' => $this->linkTypes,
            'choice_label' => 'name',
            'choice_value' => 'id',
            'disabled' => true,
        ]);
        $builder->add('default', CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Enabled by default for %name% brand', ['%name%' => $options['brand_name']]),
        ]);
        $builder->add('channel', ChannelType::class, [
            'data_class' => Channel::class,
            'label' => $this->translator->trans('Channel'),
        ]);
        if ($options['allow_choose']) {
            $builder->add('choose_channel', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'data' => false,
                'label' => $this->translator->trans('Use an existing channel'),
                'attr' => ['data-choose-channel' => ''],
            ]);
            $builder->add('channel_choice', ChannelChoiceType::class, [
                'required' => false,
                'multiple' => false,
                'mapped' => false,
                'return_object' => true,
                'label' => $this->translator->trans('Channel'),
            ]);
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $link = $event->getData();
                if ($form->get('choose_channel')?->getData() && $link instanceof ChannelLink) {
                    $link->channel = $form->get('channel_choice')->getData();
                }
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('brand_name', $this->translator->trans('this'));
        $resolver->setDefault('allow_choose', false);
    }
}
