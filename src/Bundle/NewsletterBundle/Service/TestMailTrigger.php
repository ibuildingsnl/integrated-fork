<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;

interface TestMailTrigger
{
    public function send(Newsletter $newsletter);
}
