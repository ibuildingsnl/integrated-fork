<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Integrated\Bundle\ContentBundle\Form\Type\PublicationSettingsType;
use Integrated\Common\Content\ChannelableInterface;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentPublicationIntegrationListener implements EventSubscriberInterface
{
    public function __construct(
//        private readonly PublicationRepository $publications,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_BUILD => ['buildForm', -70],
        ];
    }

    public function buildForm(BuilderEvent $event): void
    {
        $form = $event->getBuilder();
        $content = $form->getData();
        if (!$content instanceof ChannelableInterface || !$form->has('channels')) {
            return;
        }
        $form->add('publication_settings', PublicationSettingsType::class, [
            'channels' => $form->get('channels')->getOption('choices'),
            'mapped' => false,
            'attr' => [
                'class' => 'publication-settings-container',
            ],
        ]);
    }
}
