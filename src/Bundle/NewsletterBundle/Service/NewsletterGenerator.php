<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Stratadox\Clock\Clock;

class NewsletterGenerator
{
    public function __construct(
        private readonly Clock $clock,
        private readonly Renderer $renderer,
        private readonly string $baseDirectory,
    ) {
        assert(is_dir($this->baseDirectory));
    }

    public function maybeGenerate(Newsletter $newsletter): void
    {
        if (!$newsletter->isInGenerationWindow($this->clock->now())) {
            return;
        }
        $copy = $this->renderer->render($newsletter);
        $when = $newsletter->schedule->firstAfter($this->clock->now());

        $dir = $this->baseDirectory . $newsletter->getId() . $when->format('/Y/m/d/');

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($dir . $when->format('Hi') . '.html', $copy);
    }
}
