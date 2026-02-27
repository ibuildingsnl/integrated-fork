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

        $contentId = $content->getId();
        $hasPersistedIdentifier = \is_string($contentId) && '' !== $contentId;

        $form->add('publications', PublicationsType::class, [
            'channels' => $form->get('channels')->getOption('choices'),
            'mapped' => false,
            'attr' => [
                'class' => 'publication-settings-container',
            ],
            'data' => $hasPersistedIdentifier ? $this->publications->forContentByChannel($content) : [],
        ]);

        $form->add('global_publications', GlobalPublicationsType::class, [
            'channels' => $form->get('channels')->getOption('choices'),
            'mapped' => false,
            'attr' => [
                'class' => 'publication-settings-global',
            ],
        ]);

        $form->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $content = $event->getData();

            if (!$content instanceof Content) {
                return;
            }

            $contentId = $content->getId();
            $hasPersistedIdentifier = \is_string($contentId) && '' !== $contentId;
            $existingPublications = $hasPersistedIdentifier ? $this->publications->forContent($content) : [];

            $form = $event->getForm()->get('publications');
            foreach ($content->getChannels() as $channel) {
                $data = $form->get($channel->getId())->get('settings')->getData();
                $time = $content->getPublishTime();

                if (($data['time'] ?? null) instanceof PublishTimeInterface) {
                    $time = $data['time'];
                    if ($time->getStartDate() === null && $content->getPublishTime()->getStartDate() !== null) {
                        $time->setStartDate($content->getPublishTime()->getStartDate());
                    } elseif ($content->getPublishTime()->getStartDate() === null && $time->getStartDate() === null) {
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

                $existingChannelPublications = $hasPersistedIdentifier ? $this->publications->forContentOnChannel($content, $channel) : [];

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

    /**
     * @param array<string, mixed> $data
     */
    private function isPublicationChanged(Publication $previousPublication, array $data): bool
    {
        $existingSettings = $this->normalizeSettings($previousPublication->getSettings());
        $newSettings = $this->normalizeSettings((array) ($data['settings'] ?? []));

        $existingTime = $previousPublication->getTime();
        $newTime = $data['time'] ?? null;

        $timeChanged = !$this->isSamePublishTime($existingTime, $newTime instanceof PublishTimeInterface ? $newTime : null);

        $settingsChanged = $existingSettings !== $newSettings;

        return $timeChanged || $settingsChanged;
    }

    private function isSamePublishTime(?PublishTimeInterface $a, ?PublishTimeInterface $b): bool
    {
        if ($a === null || $b === null) {
            return $a === $b;
        }

        return $this->isSameDateTime($a->getStartDate(), $b->getStartDate())
            && $this->isSameDateTime($a->getEndDate(), $b->getEndDate());
    }

    private function isSameDateTime(?\DateTimeInterface $a, ?\DateTimeInterface $b): bool
    {
        if ($a === null || $b === null) {
            return $a === $b;
        }

        return $a->getTimestamp() === $b->getTimestamp();
    }

    /**
     * @param array<int|string, mixed> $settings
     *
     * @return array<int|string, mixed>
     */
    private function normalizeSettings(array $settings): array
    {
        $normalized = [];
        foreach ($settings as $key => $value) {
            $normalized[$key] = $this->normalizeSettingValue($value);
        }

        if ($this->isAssociativeArray($normalized)) {
            ksort($normalized);
        }

        return $normalized;
    }

    private function normalizeSettingValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (\is_array($value)) {
            return $this->normalizeSettings($value);
        }

        return $value;
    }

    /**
     * @param array<int|string, mixed> $values
     */
    private function isAssociativeArray(array $values): bool
    {
        if ([] === $values) {
            return false;
        }

        return array_keys($values) !== range(0, \count($values) - 1);
    }
}
