<?php

namespace Integrated\Bundle\NewsletterBundle\EventListener;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\CampaignSynchronizer;
use Integrated\Bundle\NewsletterBundle\Service\NewsletterGenerator;
use Integrated\Bundle\NewsletterBundle\Service\TestMailTrigger;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Stratadox\Clock\Clock;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NewsletterChangeListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly NewsletterGenerator $generator,
        private readonly Clock $clock,
        private readonly ?CampaignSynchronizer $campaign = null,
        private readonly ?TestMailTrigger $testMail = null,
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
        $this->campaign?->synchronize($newsletter, $newsletter->firstAfter($this->clock->now()));
        $this->testMail?->send($newsletter);
    }
}
