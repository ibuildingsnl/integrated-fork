<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;

interface CampaignUpdater
{
    public function update(Newsletter $newsletter): void;
}
