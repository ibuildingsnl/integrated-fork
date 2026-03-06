<?php

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Form\Type\ChannelType;
use Integrated\Bundle\ContentBundle\Infrastructure\ChannelTypeRegistry;
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
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ChannelTypeRegistry $linkTypeRegistry,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', ChoiceType::class, [
            'choices' => $this->linkTypeRegistry->allTypes(),
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
            'can_change_type' => false,
            'lock_name' => (bool) $options['channel_name_locked'],
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
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options): void {
                $form = $event->getForm();
                $link = $event->getData();
                if ($form->get('choose_channel')?->getData() && $link instanceof ChannelLink) {
                    $link->channel = $form->get('channel_choice')->getData();

                    return;
                }

                if (
                    $link instanceof ChannelLink
                    && $link->channel instanceof Channel
                    && (bool) $options['channel_name_locked']
                    && \is_string($options['channel_default_name'])
                    && trim($options['channel_default_name']) !== ''
                ) {
                    $link->channel->setName($options['channel_default_name']);
                }
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('brand_name', $this->translator->trans('this'));
        $resolver->setDefault('allow_choose', false);
        $resolver->setDefault('channel_name_locked', false);
        $resolver->setDefault('channel_default_name', null);
    }
}
