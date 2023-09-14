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
            $builder->add($channel->getId(), $this->publicationSettings->settingTypeFor($channel), [
                'attr' => [
                    'class' => 'publication-settings',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('channels', []);
        $resolver->setAllowedTypes('channels', ChannelInterface::class.'[]');
    }
}
