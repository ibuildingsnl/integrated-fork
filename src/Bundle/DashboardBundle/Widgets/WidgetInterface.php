<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Integrated\Bundle\UserBundle\Model\User;
use \Integrated\Common\Content\Channel\ChannelInterface;

interface WidgetInterface
{
    public function name(): string;
    public function view(): string;
    public function params(ChannelInterface $channel, User $user): array;
}
