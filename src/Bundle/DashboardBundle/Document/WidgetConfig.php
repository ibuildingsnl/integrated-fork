<?php

namespace Integrated\Bundle\DashboardBundle\Document;

class WidgetConfig
{
    public function __construct(
        private readonly string $id,
        private readonly string $widgetName,
        private readonly int $order
    ) {
    }

    public function getId(): string
    {
        return $this->id;
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
