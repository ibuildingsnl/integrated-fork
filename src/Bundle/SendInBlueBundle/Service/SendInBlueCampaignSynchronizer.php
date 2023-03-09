<?php

namespace Integrated\Bundle\SendInBlueBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\CampaignSynchronizer;
use Integrated\Bundle\NewsletterBundle\Service\CopyLocator;
use SendinBlue\Client\Api\EmailCampaignsApi;
use SendinBlue\Client\Api\SendersApi;
use SendinBlue\Client\Model\CreateEmailCampaign;
use SendinBlue\Client\Model\UpdateEmailCampaign;

final class SendInBlueCampaignSynchronizer implements CampaignSynchronizer
{
    public function __construct(
        private readonly EmailCampaignsApi $emailCampaigns,
        private readonly SendersApi $senders,
        private readonly CopyLocator $locator,
    ) {
    }

    public function synchronize(Newsletter $newsletter, \DateTimeImmutable $scheduledAt): void
    {
        foreach ($this->senders->getSenders()->getSenders() as $getSender) {
            if ($getSender->getId() === (int) $newsletter->sender) {
                $sender = [
                    'id' => $getSender->getId(),
                    'name' => $getSender->getName(),
                ];
            }
        }
        assert(isset($sender));

        $html = file_get_contents($this->locator->pathFor($newsletter));
        $title = (string) ((new \SimpleXMLElement($html))->xpath('/html/head/title')[0] ?? 'Newsletter');

        $payload = [
            'sender' => $sender,
            'subject' => $title,
            'name' => $newsletter->title,
            'htmlContent' => $html,
            'scheduledAt' => $scheduledAt->format('Y-m-d\TH:i:s\Z'),
            'recipients' => ['listIds' => [(int) $newsletter->recipientList]],
        ];
        if (null === $newsletter->externalId) {
            $response = $this->emailCampaigns->createEmailCampaign(new CreateEmailCampaign($payload));
            $newsletter->externalId = (string) $response->getId();
        } else {
            $this->emailCampaigns->updateEmailCampaign((int) $newsletter->externalId, new UpdateEmailCampaign($payload));
        }
    }
}
