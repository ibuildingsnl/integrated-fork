<?php

namespace Bundle\DashboardBundle\Document;



class WidgetConfig
{

    private string $id;

    private string $widgetName;

    private int $order;

    public function __construct(string $id, string $widgetName, string $order)
    {
        $this->id = $id;
        $this->widgetName = $widgetName;
        $this->order = $order;
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
        return $this->Order;
    }
}
