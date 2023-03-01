<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\Renderer;

class FixedRenderer implements Renderer
{
    public function __construct(private readonly string $result)
    {
    }

    public function render(Newsletter $newsletter): string
    {
        return $this->result;
    }
}
