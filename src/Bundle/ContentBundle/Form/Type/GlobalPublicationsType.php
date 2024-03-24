<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Services\PublicationSettingsProviderInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GlobalPublicationsType extends AbstractType
{
    public function __construct(
        private readonly PublicationSettingsProviderInterface $publicationSettings,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ChannelInterface $channel */
        $channelTypes = [];

        foreach ($options['channels'] as $channel) {
            $channelType = $channel->getType();

            if (\in_array($channelType, $channelTypes, true) || !$channelType->canBeSetGlobally()) {
                continue;
            }

            $channelTypes[] = $channelType;

            $builder->add('global_'.$channel->getId(), GlobalPublicationType::class, [
                'attr' => [
                    'class' => 'global-publication-settings',
                    'data-channel-type' => $channel->getType()?->getName() ?: 'N/A',
                    'data-can-be-set-globally' => $channel->getType()?->canBeSetGlobally() ? 'yes' : 'no',
                ],
                'settings' => $this->publicationSettings->settingTypeFor($channel),
                'label' => false,
                'required' => false,
                'data' => [],
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
        return 'integrated_global_publication_settings_container';
    }
}
