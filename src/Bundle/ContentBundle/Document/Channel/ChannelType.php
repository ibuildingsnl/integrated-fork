<?php

namespace Integrated\Bundle\ContentBundle\Document\Channel;

class ChannelType
{
    public ?string $icon = null;

    public function __construct(
        public string $id,
        public readonly string $name,
        public readonly bool $canBePrimary = true,
        public readonly bool $canBeSetGlobally = true,
        public readonly ?string $connector = null,
        public readonly ?string $publicationSettingsForm = null,
        ?string $icon = null,
    ) {
        $this->icon = $icon;
    }

    public function getId(): string
    {
        return isset($this->id) ? $this->id : '';
    }

    public function getName(): string
    {
        return isset($this->name) ? $this->name : '';
    }

    public function canBePrimary(): bool
    {
        return isset($this->canBePrimary) ? $this->canBePrimary : true;
    }

    public function canBeSetGlobally(): bool
    {
        return isset($this->canBeSetGlobally) ? $this->canBeSetGlobally : true;
    }

    public function getConnector(): ?string
    {
        return isset($this->connector) ? $this->connector : null;
    }

    public function getPublicationSettingsForm(): ?string
    {
        return isset($this->publicationSettingsForm) ? $this->publicationSettingsForm : null;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }
}
