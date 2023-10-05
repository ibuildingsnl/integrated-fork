<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Integrated\Common\Channel\ChannelInterface;

interface WidgetInterface
{
    public function name(): string;
    public function view(): string;
    public function params(ChannelInterface $channel): array;
}
