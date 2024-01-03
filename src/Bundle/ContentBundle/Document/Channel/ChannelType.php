<?php

namespace Integrated\Bundle\ContentBundle\Document\Channel;

class ChannelType
{
    public function __construct(
        public string $id,
        public readonly string $name,
        public readonly bool $canBePrimary = true,
        public readonly bool $canBeSetGlobally = true,
        public readonly ?string $connector = null,
        public readonly ?string $publicationSettingsForm = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function canBePrimary(): bool
    {
        return $this->canBePrimary;
    }

    public function canBeSetGlobally(): bool
    {
        return $this->canBeSetGlobally;
    }

    public function getConnector(): ?string
    {
        return $this->connector;
    }

    public function getPublicationSettingsForm(): ?string
    {
        return $this->publicationSettingsForm;
    }
}
