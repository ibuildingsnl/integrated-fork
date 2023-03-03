<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Stratadox\Clock\Clock;

final class TestMailSender
{
    public function __construct(
        private readonly NewsletterGenerator  $generator,
        private readonly Clock                $clock,
        private readonly CampaignSynchronizer $campaign,
        private readonly TestMailTrigger      $testMails,
    ) {
    }

    public function maybeSend(Newsletter $newsletter): void
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

        $this->generator->generate($newsletter);
        $this->campaign->synchronize($newsletter, $newsletter->firstAfter($this->clock->now()));
        $this->testMails->send($newsletter);
        $newsletter->lastTestMailSentAt = $this->clock->now();
    }

    public function lastTestMailTime(Newsletter $newsletter): ?\DateTimeImmutable
    {
        return $newsletter->lastTestMailSentAt;
    }
}
