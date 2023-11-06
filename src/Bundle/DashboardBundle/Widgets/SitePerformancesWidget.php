<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\UserBundle\Model\User;
use \Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Bundle\AnalyticsBundle\Document\SitePerformance;
use Symfony\Component\HttpFoundation\Request;


class SitePerformancesWidget implements WidgetInterface
{
    public function __construct(
        private readonly DocumentManager  $manager,
    ){}

    public function id(): string
    {
        return 'site_performance';
    }
    public function name(): string
    {
        return 'Site performance';
    }

    public function view(): string
    {
        return '@IntegratedDashboard/site_performance.html.twig';
    }

    public function params(ChannelInterface $channel, User $user, Request $request): array
    {
        $channelPerformances = $this->manager->getRepository(SitePerformance::class)
            ->findBy(
                ['channelID' => $channel->getId()],
                ['dateTime' => 'DESC'],
                7
            );
        if($channelPerformances != null)
        {
            $mostRecentSpeed = $this->MilliToSecond($channelPerformances[0]->getSiteSpeed());
            $averageSpeed = $this->getAverageSpeed($channelPerformances);
        }
        return [
            'mostRecentSpeed' => $mostRecentSpeed ?? "Data not found",
            'averageSpeed' => $averageSpeed ?? "Data not found"
        ];

    }

    private function getAverageSpeed(array $channelPerformances): float
    {
        $totalSpeed = 0;
        $count = 0;
        $averageSpeed = 0;
        foreach ($channelPerformances as $performance) {
            $speed = $performance->getSiteSpeed();
            if ($speed !== null) {
                $totalSpeed += $speed;
                $count++;
            }
        }
        if ($count > 0) {
            $averageSpeed = $totalSpeed / $count;
        }
        return $this->MilliToSecond($averageSpeed) ?? "No data found";
    }

    private function MilliToSecond(float $milliValue): float
    {
        return round($milliValue / 1000, 2);
    }


}
