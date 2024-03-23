<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Bundle\ContentBundle\Event\CalendarEvent;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CalendarPublicationProvider implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetManager $js,
        private readonly PublicationRepositoryInterface $publicationRepository,
        private readonly BrandRepository $brands,
    ) {
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
            $dateTime = $publication->getTime()->getStartDate();

            if ($publication->getChannel() instanceof ChannelInterface) {
                foreach ($this->brands->all() as $brand) {
                    if ($brand->hasChannel($publication->getChannel())) {
                        $currentBrand = $brand;
                        $brandProfile = $brand->profile;
                    }
                }
            }

            $status = $now > $publication->getTime()->getStartDate() ? 'published' : 'planned';
            if ($publication->getStatus() === 'failed') {
                $status = 'failed';
            }

            if (isset($brandProfile) && isset($currentBrand)) {
                if ($brandProfile instanceof BrandProfile && $currentBrand instanceof Brand) {
                    $data = [
                        'id' => $publication->getContent()->getId(),
                        'title' => $publication->getContent()->getTitle(),
                        'type' => $type->getId(),
                        'name' => $type->getName(),
                        'icon' => $type->getIcon() ?: 'empty-page',
                        'published' => $status,
                        'date' => $dateTime->format('Y/m/d'),
                        'time' => $dateTime->format('Hi'),
                        'display_time' => $dateTime->format('H:i'),
                        'brand_name' => $currentBrand->getName(),
                        'brand_favicon' => $brandProfile->getFavicon()?->getFile()->getPathname(),
                        'brand_color' => $brandProfile->getColor(),
                        'response' => $publication->getResponse()
                    ];
                    $scheduledPublications[] = $data;
                }
            }
        }
        $this->js->add('const publicationSchedule = '.json_encode($scheduledPublications), true);
        $this->js->add('bundles/integratedcontent/js/publication_calendar.js');
    }
}
