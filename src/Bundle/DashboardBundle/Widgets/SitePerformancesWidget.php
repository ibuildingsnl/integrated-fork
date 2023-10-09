<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use GuzzleHttp\Client;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Channel\ChannelInterface;



class SitePerformancesWidget implements WidgetInterface
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly Client $client
    ) {
    }
    public function name(): string
    {
        return 'site performance';
    }

    public function view(): string
    {
        return '@IntegratedDashboard/site_performance.html.twig';
    }

    public function params(ChannelInterface $channel, User $user): array
    {

        //$url = "https://" . $channel->getPrimaryDomain();
        $inputString = $channel->getId();
        if (str_contains($inputString, '_')) {
            $parts = explode('_', $inputString);
            $outputString = $parts[0];
        } else {
            $outputString = $inputString;
        }

        if ($outputString == "bakkers") { $outputString = "bakkersinbedrijf";}

        $url = "https://".$outputString.".nl";

        return [
            'channel' => $channel,
            'speedIndex' => "1,0s" ?? null,
            'content' => "test" ?? null,
            'url' => $url,
        ];

    }
}
