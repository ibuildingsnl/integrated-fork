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
        private readonly PublicationSettingsProviderInterface $provider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $ids = [];

        /** @var ChannelInterface $channel */
        foreach ($options['channels'] as $channel) {
            if (!($type = $channel->getType()) || !$type->canBeSetGlobally()) {
                continue;
            }

            if (\array_key_exists($id = $type->getId(), $ids)) {
                continue;
            }

            $ids[$id] = $id;

            $builder->add('global_'.$channel->getId(), GlobalPublicationType::class, [
                'required' => false,
                'label' => false,
                'settings' => $this->provider->settingTypeFor($channel),
                'data' => [],
                'mapped' => false,
                'attr' => [
                    'class' => 'global-publication-settings',
                    'data-channel-type' => $type->getName() ?: 'N/A',
                    'data-can-be-set-globally' => 'yes',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('channels', []);
        $resolver->setAllowedTypes('channels', ChannelInterface::class.'[]');
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_global_publication_settings_container';
    }
}
