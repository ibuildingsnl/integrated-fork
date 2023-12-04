<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\UserBundle\Model\User;
use \Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Bundle\AnalyticsBundle\Document\SitePerformance;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;



class SitePerformancesWidget implements WidgetInterface
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly DocumentManager  $manager,
    ){
        $this->id = 'site_performance';
        $this->name = 'Site performance';
        $this->view = '@IntegratedDashboard/site_performance.html.twig';
    }
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function getParams(ChannelInterface $channel, User $user, Request $request): array
    {

        $channelPerformances = $this->manager->getRepository(SitePerformance::class)
            ->findOneBy(
                ['channelID' => $channel->getId()],
                ['dateTime' => 'DESC']
            );
        if($channelPerformances != null)
        {
            $desktopSiteData = [
                'desktopSiteScore' => round(($channelPerformances->getDesktopSiteScore() * 100 ?? 0),1),
                'desktopSpeedIndex' => $this->MilliToSecond($channelPerformances->getDesktopSpeedIndex()) ?? 0,
                'desktopTimeToInteractive' => $this->MilliToSecond($channelPerformances->getDesktopTimeToInteractive()) ?? 0,
                'desktopTimeToFirstByte' => round($channelPerformances->getDesktopServerResponseTime()) ?? 0,
                'desktopTotalBlockingTime' => round($channelPerformances->getDesktopTotalBlockingTime()) ?? 0,
               ];
            $mobileSiteData = [
                'mobileSiteScore' => round(($channelPerformances->getMobileSiteScore() * 100 ?? 0),1),
                'mobileSpeedIndex' => $this->MilliToSecond($channelPerformances->getMobileSpeedIndex()) ?? 0,
                'mobileTimeToInteractive' => $this->MilliToSecond($channelPerformances->getMobileTimeToInteractive()) ?? 0,
                'mobileTimeToFirstByte' => round($channelPerformances->getMobileServerResponseTime()) ?? 0,
                'mobileTotalBlockingTime' => round($channelPerformances->getMobileTotalBlockingTime()) ?? 0,
            ];
        }
        return [
            "widget" => $this,
            'desktopSiteData' => $desktopSiteData ?? null,
            'mobileSiteData' => $mobileSiteData ?? null,
        ];
    }

      private function MilliToSecond(float $milliValue): float
    {
        return round($milliValue / 1000, 2);
    }
}
