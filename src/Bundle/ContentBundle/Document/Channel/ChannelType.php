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
        return $this->isInitialized('id') ? $this->id : '';
    }

    public function getName(): string
    {
        return $this->isInitialized('name') ? $this->name : '';
    }

    public function canBePrimary(): bool
    {
        return $this->isInitialized('canBePrimary') ? $this->canBePrimary : true;
    }

    public function canBeSetGlobally(): bool
    {
        return $this->isInitialized('canBeSetGlobally') ? $this->canBeSetGlobally : true;
    }

    public function getConnector(): ?string
    {
        return $this->isInitialized('connector') ? $this->connector : null;
    }

    public function getPublicationSettingsForm(): ?string
    {
        return $this->isInitialized('publicationSettingsForm') ? $this->publicationSettingsForm : null;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    private function isInitialized(string $property): bool
    {
        return (new \ReflectionProperty($this, $property))->isInitialized($this);
    }
}
