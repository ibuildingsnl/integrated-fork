<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Document\Content;

class ContentEditDraft
{
    private string $id;

    private array $payload = [];

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    public function __construct(
        private string $contentId,
        private string $userId,
    ) {
        $now = new \DateTime();
        $this->createdAt = $now;
        $this->updatedAt = clone $now;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getContentId(): string
    {
        return $this->contentId;
    }

    public function setContentId(string $contentId): void
    {
        $this->contentId = $contentId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
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
        $this->updatedAt = new \DateTime();
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
