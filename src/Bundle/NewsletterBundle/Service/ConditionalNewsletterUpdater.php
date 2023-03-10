<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Stratadox\Clock\Clock;

final class ConditionalNewsletterUpdater implements NewsletterUpdater
{
    public function __construct(
        private readonly Clock                          $clock,
        private readonly SynchronizingNewsletterUpdater $updater,
    ) {
    }

    public function update(Newsletter $newsletter): void
    {
        if (!$newsletter->isInGenerationWindow($this->clock->now())) {
            return;
        }

        if (
            $newsletter->lastTestMailSentAt !== null &&
            $newsletter->firstAfter($newsletter->lastTestMailSentAt) == $newsletter->firstAfter($this->clock->now())
        ) {
            return;
        }

        $this->updater->update($newsletter);
    }
}
