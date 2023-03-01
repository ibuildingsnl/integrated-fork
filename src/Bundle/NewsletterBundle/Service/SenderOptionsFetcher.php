<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Service\Exception\EmailPlatformException;

interface SenderOptionsFetcher
{
    /**
     * @return string[] Associative array of [id => email]
     *
     * @throws EmailPlatformException
     */
    public function retrieve(): array;
}
