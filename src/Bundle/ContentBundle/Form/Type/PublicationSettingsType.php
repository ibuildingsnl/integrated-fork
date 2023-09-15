<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Services\PublicationSettingsProvider;
use Integrated\Common\Channel\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicationSettingsType extends AbstractType
{
    public function __construct(
        private readonly PublicationSettingsProvider $publicationSettings,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ChannelInterface $channel */
        foreach ($options['channels'] as $channel) {
            $builder->add($channel->getId(), PublicationSettingsPopupType::class, [
                'attr' => [
                    'class' => 'publication-settings',
                    'data-publication-channel' => $channel->getId(),
                ],
                'settings' => $this->publicationSettings->settingTypeFor($channel),
                'label' => $channel->getName(),
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('channels', []);
        $resolver->setAllowedTypes('channels', ChannelInterface::class.'[]');
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_publication_settings_container';
    }
}
