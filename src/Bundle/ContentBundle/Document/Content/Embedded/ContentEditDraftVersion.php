<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Document\Content\Embedded;

class ContentEditDraftVersion
{
    private string $id;

    private array $payload = [];

    private \DateTime $savedAt;

    /**
     * @param array<mixed> $payload
     */
    public function __construct(array $payload = [])
    {
        $this->id = bin2hex(random_bytes(8));
        $this->payload = $payload;
        $this->savedAt = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return array<mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array<mixed> $payload
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function getSavedAt(): \DateTime
    {
        return $this->savedAt;
    }

    public function setSavedAt(\DateTime $savedAt): void
    {
        $this->savedAt = $savedAt;
    }
}

