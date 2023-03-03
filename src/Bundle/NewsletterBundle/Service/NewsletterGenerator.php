<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;

class NewsletterGenerator
{
    public function __construct(
        private readonly Renderer    $renderer,
        private readonly CopyLocator $locator,
    ) {
    }

    public function generate(Newsletter $newsletter): void
    {
        file_put_contents($this->locator->pathFor($newsletter), $this->renderer->render($newsletter));
    }
}
