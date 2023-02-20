<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;

interface Renderer
{
    public function render(Newsletter $newsletter): string;
}
