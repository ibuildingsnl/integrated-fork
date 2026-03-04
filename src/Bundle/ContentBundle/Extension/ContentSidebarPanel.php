<?php

namespace Integrated\Bundle\ContentBundle\Extension;

final class ContentSidebarPanel
{
    private string $id;
    private string $title;
    private string $template;
    /** @var array<string, mixed> */
    private array $context;
    private int $priority;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(string $id, string $title, string $template, array $context = [], int $priority = 0)
    {
        $id = trim($id);
        if ('' === $id) {
            throw new \InvalidArgumentException('Sidebar panel id can not be empty.');
        }

        $this->id = $id;
        $this->title = $title;
        $this->template = $template;
        $this->context = $context;
        $this->priority = $priority;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
