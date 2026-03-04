<?php

declare(strict_types=1);

namespace Symfony\UX\LiveComponent\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsLiveComponent
{
    public function __construct(
        public string $name,
        public ?string $template = null,
    ) {
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class LiveProp
{
    public function __construct(
        public bool $writable = false,
    ) {
    }
}

