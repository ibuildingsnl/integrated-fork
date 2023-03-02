<?php

namespace Integrated\Bundle\NewsletterBundle\EventListener;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\CampaignUpdater;
use Integrated\Bundle\NewsletterBundle\Service\NewsletterGenerator;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Stratadox\Clock\Clock;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NewsletterChangeListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly NewsletterGenerator $generator,
        private readonly CampaignUpdater $campaign,
        private readonly Clock $clock,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_VALIDATE => 'onPostValidate',
        ];
    }

    public function onPostValidate(ValidationEvent $event): void
    {
        $newsletter = $event->getContent();

        if (!$newsletter instanceof Newsletter) {
            return;
        }
        if (!$newsletter->isInGenerationWindow($this->clock->now())) {
            return;
        }

        $this->generator->generate($newsletter);
        $this->campaign->update($newsletter);
    }
}
