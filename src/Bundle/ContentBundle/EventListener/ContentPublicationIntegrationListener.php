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
                    if ($time->getStartDate() === null && $content->getPublishTime()->getStartDate() !== null) {
                        $time->setStartDate($content->getPublishTime()->getStartDate());
                    } elseif ($content->getPublishTime()->getStartDate() === null) {
                        $time->setStartDate(new \DateTime());
                    }
                    unset($data['time']);
                }

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

                $existingChannelPublications = $this->publications->forContentOnChannel($content, $channel);

                if (!$existingChannelPublications) {
                    $this->publications->add(new Publication($content, $channel, $time, \is_array($data) ? $data : []));
                    continue;
                }

                foreach ($existingPublications as $key => $existingPublication) {
                    if ($existingPublication->getChannel()->getId() == $channel->getId()) {
                        unset($existingPublications[$key]);
                    }
                }

                $publicationChanged = [];
                foreach ($existingChannelPublications as $key => $previousPublication) {
                    // We're fetching the previous publication fresh from the database and persist it, because it got updated by the form and we don't want any of that.
                    $this->documentManager->refresh($previousPublication);
                    $this->documentManager->persist($previousPublication);

                    $publicationChanged[$previousPublication->getId()] = [
                        'key' => $key,
                        'changed' => $this->isPublicationChanged($previousPublication, ['settings' => $data, 'time' => $time]),
                        'status' => $previousPublication->getStatus(),
                    ];
                }

                $allPublicationsAreDifferent = !\in_array(false, array_column($publicationChanged, 'changed'), true);

                if ($allPublicationsAreDifferent) {
                    $this->publications->add(new Publication($content, $channel, $time, \is_array($data) ? $data : []));
                }

                foreach ($publicationChanged as $publication) {
                    if (!$allPublicationsAreDifferent || $publication['status'] === 'success' || $publication['changed'] === false) {
                        unset($existingChannelPublications[$publication['key']]);
                    }
                }

                foreach ($existingChannelPublications as $publicationToRemove) {
                    $this->publications->remove($publicationToRemove);
                }
            }

            foreach ($existingPublications as $publicationToRemove) {
                if ($publicationToRemove->getStatus() !== 'success') {
                    $this->publications->remove($publicationToRemove);
                }
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
