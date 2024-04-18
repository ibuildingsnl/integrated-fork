<?php

namespace Integrated\Bundle\ContentBundle\Infrastructure;

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;

class ChannelTypeFactory
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly bool $canBePrimary = true,
        public readonly bool $canBeSetGlobally = true,
        public readonly ?string $connector = null,
        public readonly ?string $publicationSettingsForm = null,
        public readonly ?string $icon = null,
    ) {
    }

    public function create(): ChannelType
    {
        return new ChannelType(
            $this->id,
            $this->name,
            $this->canBePrimary,
            $this->canBeSetGlobally,
            $this->connector,
            $this->publicationSettingsForm,
            $this->icon,
        );
    }
}
