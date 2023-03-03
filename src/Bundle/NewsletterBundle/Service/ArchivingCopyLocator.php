<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Stratadox\Clock\Clock;

final class ArchivingCopyLocator implements CopyLocator
{
    public function __construct(
        private readonly string $baseDirectory,
        private readonly Clock $clock,
    ) {
        assert(is_dir($this->baseDirectory));
    }

    public function pathFor(Newsletter $newsletter): string
    {
        $sendTime = $newsletter->firstAfter($this->clock->now());

        $dir = $this->baseDirectory . $newsletter->getId() . $sendTime->format('/Y/m/d/');

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir . $sendTime->format('Hi') . '.html';
    }
}
