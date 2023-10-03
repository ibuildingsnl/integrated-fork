<?php

namespace Bundle\DashboardBundle\Widgets;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Common\Channel\ChannelInterface;


class Widget implements WidgetInterface
{
    private $name;
    private $widgetView;

    public function __construct(private readonly DocumentManager $repository, string $name, string $widgetView)
    {
        $this->name = $name;
        $this->widgetView = $widgetView;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function view(): string
    {
        return $this->widgetView;
    }

    public function params(ChannelInterface $channel): array
    {
        return $this->repository->findBy(['channel' => $channel]);
    }
}
