<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\TestMailTrigger;

final class SpyingTestMailTrigger implements TestMailTrigger
{
    private array $triggered = [];

    public function send(Newsletter $newsletter, string ...$recipients)
    {
        $this->triggered[$newsletter->getId()] = true;
    }

    public function wasTriggeredFor(Newsletter $newsletter): bool
    {
        return $this->triggered[$newsletter->getId()] ?? false;
    }

    public function reset(Newsletter $newsletter): void
    {
        $this->triggered[$newsletter->getId()] = false;
    }
}
