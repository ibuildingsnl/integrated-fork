<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;

interface CampaignSynchronizer
{
    public function synchronize(Newsletter $newsletter): void;
}
