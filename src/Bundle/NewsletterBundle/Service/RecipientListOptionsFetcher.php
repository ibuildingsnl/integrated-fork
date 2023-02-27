<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Service\Exception\EmailPlatformException;

interface RecipientListOptionsFetcher
{
    /**
     * @param int $amount The number of lists to retrieve
     * @param int $start  The number of lists to skip
     * @return string[]   Associative array of [id => name]
     *
     * @throws EmailPlatformException
     */
    public function retrieve(int $amount = 50, int $start = 0): array;
}
