<?php

namespace Integrated\Bundle\SendInBlueBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\CampaignSynchronizer;
use Integrated\Bundle\NewsletterBundle\Service\CopyLocator;
use SendinBlue\Client\Api\EmailCampaignsApi;
use SendinBlue\Client\Model\CreateEmailCampaign;
use SendinBlue\Client\Model\CreateEmailCampaignSender;
use SendinBlue\Client\Model\UpdateEmailCampaign;
use SendinBlue\Client\Model\UpdateEmailCampaignSender;

final class SendInBlueCampaignSynchronizer implements CampaignSynchronizer
{
    public function __construct(
        private readonly EmailCampaignsApi $emailCampaigns,
        private readonly CopyLocator       $locator,
    ) {
    }

    public function synchronize(Newsletter $newsletter, \DateTimeImmutable $scheduledAt): void
    {
        if (null === $newsletter->externalId) {
            // create
            $response = $this->emailCampaigns->createEmailCampaign(new CreateEmailCampaign([
                'sender' => new CreateEmailCampaignSender(['email' => $newsletter->sender]),
                'subject' => '@todo',
                'name' => $newsletter->title,
                'htmlContent' => file_get_contents($this->locator->pathFor($newsletter)),
                'scheduledAt' => $scheduledAt->format('Y-m-d\TH:i:s\Z'),
                'recipients' => ['listIds' => [(int) $newsletter->recipientList]],
            ]));
            $newsletter->externalId = (string) $response->getId();
        } else {
            $this->emailCampaigns->updateEmailCampaign((int) $newsletter->externalId, new UpdateEmailCampaign([
                'sender' => new UpdateEmailCampaignSender(['email' => $newsletter->sender]),
                'subject' => '@todo',
                'name' => $newsletter->title,
                'htmlContent' => file_get_contents($this->locator->pathFor($newsletter)),
                'scheduledAt' => $scheduledAt->format('Y-m-d\TH:i:s\Z'),
                'recipients' => ['listIds' => [(int) $newsletter->recipientList]],
            ]));
        }
    }
}
