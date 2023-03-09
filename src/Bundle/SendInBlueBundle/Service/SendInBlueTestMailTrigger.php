<?php

namespace Integrated\Bundle\SendInBlueBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\TestMailTrigger;
use SendinBlue\Client\Api\EmailCampaignsApi;
use SendinBlue\Client\Model\SendTestEmail;

final class SendInBlueTestMailTrigger implements TestMailTrigger
{
    public function __construct(
        private readonly EmailCampaignsApi $emailCampaigns,
    ) {
    }

    public function send(Newsletter $newsletter)
    {
        assert(null !== $newsletter->externalId);
        if (!empty($newsletter->testAddresses)) {
            $this->emailCampaigns->sendTestEmail((int)$newsletter->externalId, new SendTestEmail([
                'emailTo' => $newsletter->testAddresses,
            ]));
        }
    }
}
