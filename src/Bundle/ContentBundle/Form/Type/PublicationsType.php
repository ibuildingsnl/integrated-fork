<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Services\PublicationSettingsProvider;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicationsType extends AbstractType
{
    public function __construct(
        private readonly PublicationSettingsProvider $publicationSettings,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ChannelInterface $channel */
        foreach ($options['channels'] as $channel) {
            $builder->add($channel->getId(), PublicationType::class, [
                'attr' => [
                    'class' => 'publication-settings',
                    'data-publication-channel' => $channel->getId(),
                    'data-channel-type' => $channel->getType()?->getName() ?: 'N/A',
                    'data-can-be-set-globally' => $channel->getType()?->canBeSetGlobally() ? 'yes' : 'no',
                    'data-channel-name' => $channel->getName(),
                ],
                'settings' => $this->publicationSettings->settingTypeFor($channel),
                'label' => $channel->getName(),
                'required' => false,
                'data' => $options['data'][$channel->getId()] ?? [],
                'mapped' => false,
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
