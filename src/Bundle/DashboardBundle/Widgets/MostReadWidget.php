<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;


class MostReadWidget implements WidgetInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function name(): string
    {
        return 'most read';
    }

    public function view(): string
    {
        return '@IntegratedDashboard/most_read.html.twig';
    }

    public function params(ChannelInterface $channel, User $user): array
    {

        $mostRead = [];
        return [
            "mostRead" => $mostRead
        ];
    }
}
