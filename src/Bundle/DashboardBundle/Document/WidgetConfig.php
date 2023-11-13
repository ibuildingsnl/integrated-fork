<?php

namespace Integrated\Bundle\DashboardBundle\Document;

class WidgetConfig
{
    private string $id;

    public function __construct(
        private readonly string $widgetId,
        private readonly string $widgetName,
        private readonly int $order
    ) {
    }

    public function getId(): ?string
    {
        return $this->id;
    }
    public function getWidgetId(): string
    {
        return $this->widgetId;
    }
    public function getWidgetName(): string
    {
        return $this->widgetName;
    }
    public function getOrder(): int
    {
        return $this->order;
    }

}
