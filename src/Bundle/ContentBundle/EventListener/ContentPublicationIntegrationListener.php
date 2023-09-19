<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Publication;
use Integrated\Bundle\ContentBundle\Form\Type\PublicationSettingsType;
use Integrated\Common\Content\ChannelableInterface;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ContentPublicationIntegrationListener implements EventSubscriberInterface
{
    public function __construct(
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
        $form->add('publications', PublicationSettingsType::class, [
            'channels' => $form->get('channels')->getOption('choices'),
            'attr' => [
                'class' => 'publication-settings-container',
            ],
        ]);

        $form->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $content = $event->getData();
            if (!$content instanceof Content) {
                return;
            }
            $form = $event->getForm()->get('publications');
            foreach ($content->getChannels() as $channel) {
                $data = $form->get($channel->getId())->get('settings')->getData();
                $time = $content->getPublishTime();
                if ($data instanceof PublishTime) {
                    $time = $data;
                } elseif (($data['time'] ?? null) instanceof PublishTime) {
                    $time = $data['time'];
                    unset($data['time']);
                }
//                $content->addPublication(
//                    new Publication($channel, $time, is_array($data) ? $data : [])
//                );
            }
        });
    }
}
