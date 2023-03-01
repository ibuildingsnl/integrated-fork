<?php

namespace Integrated\Bundle\SendInBlueBundle\Service;

use Integrated\Bundle\NewsletterBundle\Service\Exception\EmailPlatformException;
use Integrated\Bundle\NewsletterBundle\Service\RecipientListOptionsFetcher;
use SendinBlue\Client\Api\ContactsApi;

final class SendInBlueRecipientListFetcher implements RecipientListOptionsFetcher
{
    public function __construct(private readonly ContactsApi $contacts)
    {
    }

    public function retrieve(int $amount = 50, int $start = 0): array
    {
        $lists = [];
        try {
            foreach ($this->contacts->getLists()->getLists() as $list) {
                $lists[$list['id']] = $list['name'];
            }
        } catch (\Throwable $e) {
            throw EmailPlatformException::from($e);
        }
        return $lists;
    }
}
