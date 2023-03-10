<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Stratadox\Clock\Clock;

final class SynchronizingNewsletterUpdater implements NewsletterUpdater
{
    public function __construct(
        private readonly NewsletterGenerator $generator,
        private readonly Clock $clock,
        private readonly ?CampaignSynchronizer $campaign = null,
        private readonly ?TestMailTrigger $testMail = null,
    ) {
    }

    public function update(Newsletter $newsletter): void
    {
        if (!$newsletter->isInGenerationWindow($this->clock->now())) {
            return;
        }

        $this->generator->generate($newsletter);
        $this->campaign?->synchronize($newsletter, $newsletter->firstAfter($this->clock->now()));
        $this->testMail?->send($newsletter);
        $newsletter->lastTestMailSentAt = $this->clock->now();
    }
}
