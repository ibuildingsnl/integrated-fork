<?php

namespace Integrated\Bundle\ContentBundle\Extension;

final class ContentNavigatorColumn
{
    private string $key;
    private string $label;
    private int $priority;
    private string $emptyValue;

    public function __construct(string $key, string $label, int $priority = 0, string $emptyValue = '—')
    {
        $key = trim($key);
        if ('' === $key) {
            throw new \InvalidArgumentException('Navigator column key can not be empty.');
        }

        $this->key = $key;
        $this->label = $label;
        $this->priority = $priority;
        $this->emptyValue = $emptyValue;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getEmptyValue(): string
    {
        return $this->emptyValue;
    }
}
