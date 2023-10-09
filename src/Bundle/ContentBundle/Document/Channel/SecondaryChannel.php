<?php

namespace Integrated\Bundle\ContentBundle\Document\Channel;

class SecondaryChannel extends Channel
{
    public function __construct(
        private readonly string $type,
    ) {
        parent::__construct();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function canBePrimary(): bool
    {
        return false;
    }
}
