<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\HttpFoundation\Request;

interface WidgetInterface
{
    public function getId(): string;

    public function getName(): string;

    public function getView(): string;

    public function getParams(ChannelInterface $channel, User $user, Request $request): array;
}
