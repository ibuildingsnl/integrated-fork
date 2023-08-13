<?php

namespace Integrated\Bundle\BrandBundle\Event;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Symfony\Contracts\EventDispatcher\Event;

final class BrandAddedEvent extends Event
{
    public function __construct(
        public readonly Brand $brand,
    ) {}
}
