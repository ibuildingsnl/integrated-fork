<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Bundle\ContentBundle\Event\CalendarEvent;
use Integrated\Bundle\ImageBundle\Twig\Extension\ImageExtension;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CalendarPublicationProvider implements EventSubscriberInterface
{
    private readonly DocumentRepository $documentRepository;

    public function __construct(
        private readonly AssetManager $js,
        private readonly PublicationRepositoryInterface $publicationRepository,
        private readonly BrandRepository $brands,
        private readonly DocumentManager $documentManager,
        private readonly ImageExtension $imageExtension,
    ) {
        $this->documentRepository = $this->documentManager->getRepository(File::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEvent::PREPARED_WEEK_OPTIONS => 'addPublicationSchedule',
            CalendarEvent::PREPARED_MONTH_OPTIONS => 'addPublicationSchedule',
        ];
    }

    public function addPublicationSchedule(CalendarEvent $event): void
    {
        /** @var \DateTimeImmutable $calendarStart */
        $calendarStart = $event->options['start'];
        /** @var \DateTimeImmutable $calendarEnd */
        $calendarEnd = $event->options['end'];

        $publications = $this->publicationRepository->forDateRange($calendarStart, $calendarEnd);

        $now = new \DateTime();

        $scheduledPublications = [];

        foreach ($publications as $publication) {
            $type = $publication->getChannel()->getType();

            if ($publication->getChannel() === null || $publication->getContent() === null || $type === null) {
                continue;
            }

            $dateTime = $publication->getTime()->getStartDate();
            $eligibleForDisplay = false;

            if ($publication->getChannel() instanceof ChannelInterface) {
                foreach ($this->brands->all() as $brand) {
                    if ($brand->hasChannel($publication->getChannel())) {
                        if (\array_key_exists('brands', $event->options) && \in_array($brand->getId(), $event->options['brands'])) {
                            $eligibleForDisplay = true;
                        }
                        $currentBrand = $brand;
                        $brandProfile = $brand->getProfile();
                    }
                }
            }

            if (!\array_key_exists('brands', $event->options)) {
                $eligibleForDisplay = true;
            }

            if (!$eligibleForDisplay) {
                continue;
            }

            $status = $now > $publication->getTime()->getStartDate() ? 'published' : 'planned';
            if ($publication->getStatus() === 'failed') {
                $status = 'failed';
            }

            $publicationSettings = $publication->getSettings();
            $urls = [];
            $images = [];

            if (isset($publicationSettings['images']) && \count($publicationSettings['images']) > 0) {
                $imageIds = [];

                foreach ($publicationSettings['images'] as $image) {
                    $imageIds[] = $image['$id'];
                }

                $images = $this->documentRepository
                    ->createQueryBuilder()
                    ->field('id')
                    ->in($imageIds)
                    ->getQuery()
                    ->getIterator()
                    ->toArray();

                unset($publicationSettings['images']);
            } else {
                $images[] = $publication->getContent()->getFeaturedImage();
            }

            foreach ($images as $image) {
                if (!$image instanceof Image) {
                    continue;
                }
                $editedImage = $this->imageExtension->image($image->getFile())
                                                    ->cropResize(256, 256)
                                                    ->jpeg();
                $urls[] = "{$editedImage}";
            }

            if (isset($brandProfile) && isset($currentBrand)) {
                if ($brandProfile instanceof BrandProfile && $currentBrand instanceof Brand) {
                    $data = [
                        'id' => $publication->getContent()?->getId(),
                        'title' => $publication->getContent()?->getTitle(),
                        'premium' => $publication->getContent()?->isPremium(),
                        'type' => $type->getId(),
                        'typename' => $type->getName(),
                        'settings' => $publicationSettings,
                        'images' => $urls,
                        'icon' => $type->getIcon() ?: 'empty-page',
                        'published' => $status,
                        'date' => $dateTime->format('Y/m/d'),
                        'time' => $dateTime->format('Hi'),
                        'display_time' => $dateTime->format('H:i'),
                        'brand_name' => $currentBrand->getName(),
                        'brand_favicon' => $brandProfile->getFavicon()?->getFile()->getPathname(),
                        'brand_color' => $brandProfile->getColor(),
                        'response' => $publication->getResponse(),
                    ];
                    $scheduledPublications[] = $data;
                }
            }
        }
        $this->js->add('const publicationSchedule = '.json_encode($scheduledPublications), true);
        $this->js->add('bundles/integratedcontent/js/publication_calendar.js');
    }
}
