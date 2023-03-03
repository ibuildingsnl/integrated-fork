<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\CampaignSynchronizer;

final class SpyingCampaignSynchronizer implements CampaignSynchronizer
{
    private array $updated = [];

    public function synchronize(Newsletter $newsletter): void
    {
        $this->updated[$newsletter->getId()] = true;
    }

    public function wasUpdated(Newsletter $newsletter): bool
    {
        return $this->updated[$newsletter->getId()] ?? false;
    }

    public function reset(Newsletter $newsletter): void
    {
        $this->updated[$newsletter->getId()] = false;
    }
}
