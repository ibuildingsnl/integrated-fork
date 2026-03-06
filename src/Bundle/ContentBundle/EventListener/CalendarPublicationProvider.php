<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Event\CalendarEvent;
use Integrated\Bundle\ImageBundle\Twig\Extension\ImageExtension;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CalendarPublicationProvider implements EventSubscriberInterface
{
    private const DEFAULT_SCHEDULE_LIMIT = 50;
    private const MAX_SCHEDULE_LIMIT = 50;
    private const MAX_SCAN_LIMIT = 200;
    private const SCAN_MULTIPLIER = 4;

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
        $brands = $this->brands->all();
        $selectedBrandIds = $this->getSelectedBrandIds($event->options);
        $brandContextByChannel = [];

        $now = new \DateTime();
        $scheduleLimit = $this->normalizeScheduleLimit($event->options['limit'] ?? null);
        $scanLimit = $this->getScanLimit($scheduleLimit);

        $scheduledPublications = [];
        $scannedPublications = 0;

        foreach ($publications as $publication) {
            ++$scannedPublications;
            if ($scannedPublications > $scanLimit) {
                break;
            }

            if (\count($scheduledPublications) >= $scheduleLimit) {
                break;
            }

            $currentBrand = null;
            $brandProfile = null;
            $channel = $publication->getChannel();
            $channelId = $channel->getId();

            if (!$type = $channel->getType()) {
                continue;
            }

            if (!\is_string($channelId) || '' === $channelId) {
                continue;
            }

            if (!\array_key_exists($channelId, $brandContextByChannel)) {
                $brandContextByChannel[$channelId] = $this->resolveBrandContext($channel, $brands, $selectedBrandIds);
            }

            $brandContext = $brandContextByChannel[$channelId];
            if (!\is_array($brandContext)) {
                continue;
            }

            $currentBrand = $brandContext['brand'];
            $brandProfile = $brandContext['profile'];
            $content = $publication->getContent();

            $title = $content->__toString();
            if (!\is_string($title) || '' === trim($title)) {
                $title = $content->getId();
            }

            if (!\is_string($title) || '' === $title) {
                continue;
            }

            $dateTime = $publication->getTime()->getStartDate();

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
            } elseif ($content instanceof Article || $content instanceof Taxonomy) {
                $images[] = $content->getFeaturedImage();
            }

            foreach ($images as $image) {
                if (!$image instanceof Image) {
                    continue;
                }

                $urls[] = $this->imageExtension->image($image->getFile())
                                               ->cropResize(256, 256)
                                               ->jpeg();
            }

            if ($brandProfile instanceof BrandProfile && $currentBrand instanceof Brand) {
                $data = [
                    'id' => $content->getId(),
                    'title' => $title,
                    'premium' => $content->isPremium(),
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
        $this->js->add('window.publicationSchedule = '.json_encode($scheduledPublications), true);
        $this->js->add('bundles/integratedcontent/js/publication_calendar.js');
    }

    /** @return array<string, true>|null */
    private function getSelectedBrandIds(array $options): ?array
    {
        if (!\array_key_exists('brands', $options) || !\is_array($options['brands']) || [] === $options['brands']) {
            return null;
        }

        $result = [];
        foreach ($options['brands'] as $brandId) {
            if (\is_string($brandId) && '' !== $brandId) {
                $result[$brandId] = true;
            }
        }

        return [] === $result ? null : $result;
    }

    /**
     * @param Brand[]                  $brands
     * @param array<string, true>|null $selectedBrandIds
     *
     * @return array{brand: Brand, profile: BrandProfile}|null
     */
    private function resolveBrandContext(ChannelInterface $channel, array $brands, ?array $selectedBrandIds): ?array
    {
        foreach ($brands as $brand) {
            if ($selectedBrandIds && !isset($selectedBrandIds[$brand->getId()])) {
                continue;
            }

            if (!$brand->hasChannel($channel)) {
                continue;
            }

            $brandProfile = $brand->getProfile();
            if (!$brandProfile instanceof BrandProfile) {
                continue;
            }

            return [
                'brand' => $brand,
                'profile' => $brandProfile,
            ];
        }

        return null;
    }

    private function normalizeScheduleLimit(mixed $value): int
    {
        if (\is_int($value)) {
            return max(1, min(self::MAX_SCHEDULE_LIMIT, $value));
        }

        if (\is_string($value) && ctype_digit($value)) {
            return max(1, min(self::MAX_SCHEDULE_LIMIT, (int) $value));
        }

        if (is_numeric($value)) {
            return max(1, min(self::MAX_SCHEDULE_LIMIT, (int) $value));
        }

        return self::DEFAULT_SCHEDULE_LIMIT;
    }

    private function getScanLimit(int $scheduleLimit): int
    {
        return max(1, min(self::MAX_SCAN_LIMIT, $scheduleLimit * self::SCAN_MULTIPLIER));
    }
}
