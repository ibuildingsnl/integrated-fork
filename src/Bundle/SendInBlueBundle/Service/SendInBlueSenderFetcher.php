<?php

namespace Integrated\Bundle\SendInBlueBundle\Service;

use Integrated\Bundle\NewsletterBundle\Service\Exception\EmailPlatformException;
use Integrated\Bundle\NewsletterBundle\Service\SenderOptionsFetcher;
use SendinBlue\Client\Api\SendersApi;

final class SendInBlueSenderFetcher implements SenderOptionsFetcher
{
    public function __construct(private readonly SendersApi $senders)
    {
    }

    public function retrieve(): array
    {
        $senders = [];
        try {
            foreach ($this->senders->getSenders()->getSenders() as $sender) {
                $senders[$sender->getId()] = $sender->getEmail();
            }
        } catch (\Throwable $e) {
            throw EmailPlatformException::from($e);
        }
        return $senders;
    }
}
