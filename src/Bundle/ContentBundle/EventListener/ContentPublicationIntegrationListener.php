<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepository;
use Integrated\Bundle\ContentBundle\Form\Type\PublicationsType;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Integrated\Common\Content\PublishTimeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ContentPublicationIntegrationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly PublicationRepository $publications,
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
        if (!$content instanceof Content || !$form->has('channels')) {
            return;
        }
        $form->add('publications', PublicationsType::class, [
            'channels' => $form->get('channels')->getOption('choices'),
            'mapped' => false,
            'attr' => [
                'class' => 'publication-settings-container',
            ],
            'data' => $this->publications->forContentByChannel($content),
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
                if (($data['time'] ?? null) instanceof PublishTimeInterface) {
                    $time = $data['time'];
                    unset($data['time']);
                }
                $previous = $this->publications->forContentOnChannel($content, $channel);
                if (count($previous)) {
                    // No duplicate publications
                    foreach ($previous as $previousPublication) {
                        $this->publications->remove($previousPublication);
                    }
                }
                $this->publications->add(
                    new Publication($content, $channel, $time, is_array($data) ? $data : [])
                );
            }
        });
    }
}
