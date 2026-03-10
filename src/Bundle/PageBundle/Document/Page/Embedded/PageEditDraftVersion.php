<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Document\Page\Embedded;

class PageEditDraftVersion
{
    private string $id;

    private array $gridPayload = [];

    private array $menuPayload = [];

    private \DateTime $savedAt;

    /**
     * @param array<mixed> $gridPayload
     * @param array<mixed> $menuPayload
     */
    public function __construct(array $gridPayload = [], array $menuPayload = [])
    {
        $this->id = bin2hex(random_bytes(8));
        $this->gridPayload = $gridPayload;
        $this->menuPayload = $menuPayload;
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
    public function getGridPayload(): array
    {
        return $this->gridPayload;
    }

    /**
     * @param array<mixed> $gridPayload
     */
    public function setGridPayload(array $gridPayload): void
    {
        $this->gridPayload = $gridPayload;
    }

    /**
     * @return array<mixed>
     */
    public function getMenuPayload(): array
    {
        return $this->menuPayload;
    }

    /**
     * @param array<mixed> $menuPayload
     */
    public function setMenuPayload(array $menuPayload): void
    {
        $this->menuPayload = $menuPayload;
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
