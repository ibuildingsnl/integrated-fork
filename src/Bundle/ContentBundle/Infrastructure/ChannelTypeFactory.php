<?php

namespace Integrated\Bundle\ContentBundle\Infrastructure;

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;

class ChannelTypeFactory
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly bool $canBePrimary = true,
        public readonly ?string $connector = null,
        public readonly ?string $publicationSettingsForm = null,
    ) {
    }

    public function create(): ChannelType
    {
        return new ChannelType(
            $this->id,
            $this->name,
            $this->canBePrimary,
            $this->connector,
            $this->publicationSettingsForm,
        );
    }
}
