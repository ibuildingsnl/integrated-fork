<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Bundle\ContentBundle\Form\Type\GlobalPublicationsType;
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
        private readonly PublicationRepositoryInterface $publications,
        private readonly DocumentManager $documentManager,
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

        $form->add('global_publications', GlobalPublicationsType::class, [
            'channels' => $form->get('channels')->getOption('choices'),
            'mapped' => false,
            'attr' => [
                'class' => 'publication-settings-global',
            ],
        ]);

        $form->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $content = $event->getData();

            if (!$content instanceof Content) {
                return;
            }

            $existingPublications = $this->publications->forContent($content);

            $form = $event->getForm()->get('publications');
            foreach ($content->getChannels() as $channel) {
                $data = $form->get($channel->getId())->get('settings')->getData();
                $time = $content->getPublishTime();

                if (($data['time'] ?? null) instanceof PublishTimeInterface) {
                    $time = $data['time'];
                    unset($data['time']);
                }

                //TODO: Changing time is not seen as a "change" yet.

                $imagesProcessed = [];
                if (isset($data['images']) && \is_array($data['images'])) {
                    foreach ($data['images'] as $key => $image) {
                        $imagesProcessed[$key] = [
                            '$ref' => 'content',
                            '$id' => $image->getId(),
                            'class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image',
                        ];
                    }
                    $data['images'] = $imagesProcessed;
                }

                $foundOrUpdated = false;
                foreach ($existingPublications as $key => $previousPublication) {
                    if ($previousPublication->getChannel()->getId() == $channel->getId()) {

                        $this->documentManager->refresh($previousPublication);

                        if ($this->isPublicationChanged($previousPublication, ['settings' => $data, 'time' => $time])) {
                            $this->publications->remove($previousPublication);

                            $this->publications->add(
                                new Publication($content, $channel, $time, \is_array($data) ? $data : [])
                            );
                        }

                        unset($existingPublications[$key]);
                        $foundOrUpdated = true;
                        break;
                    }
                }

                if (!$foundOrUpdated) {
                    $this->publications->add(
                        new Publication($content, $channel, $time, \is_array($data) ? $data : [])
                    );
                }
            }

            foreach ($existingPublications as $publicationToRemove) {
                $this->publications->remove($publicationToRemove);
            }
        });
    }

    private function isPublicationChanged($previousPublication, $data): bool
    {
        $existingSettings = $previousPublication->getSettings();
        $newSettings = $data['settings'] ?? null;

        $existingTime = $previousPublication->getTime();
        $newTime = $data['time'] ?? null;

        $timeChanged = $existingTime != $newTime;

        $settingsChanged = json_encode($existingSettings) != json_encode($newSettings);

        return $timeChanged || $settingsChanged;
    }
}
