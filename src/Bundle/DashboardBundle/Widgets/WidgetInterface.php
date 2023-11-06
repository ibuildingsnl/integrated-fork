<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Integrated\Bundle\UserBundle\Model\User;
use \Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\HttpFoundation\Request;

interface WidgetInterface
{
    public function id(): string;
    public function name(): string;
    public function view(): string;
    public function params(ChannelInterface $channel, User $user, Request $request): array;
}
