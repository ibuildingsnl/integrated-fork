<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;

interface CopyLocator
{
    public function pathFor(Newsletter $newsletter): string;
}
