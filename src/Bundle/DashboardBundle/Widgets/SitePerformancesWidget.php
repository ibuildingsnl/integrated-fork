<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\Persistence\ObjectRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Channel\ChannelInterface;
use Integrated\Bundle\AnalyticsBundle\Document\SitePerformance;
use Stratadox\Clock\Clock;


class SitePerformancesWidget implements WidgetInterface
{
    public function __construct(
        private readonly DocumentManager  $manager,
        private readonly ObjectRepository $channelRepository,
        private readonly Client           $client,
    )
    {
    }

    public function name(): string
    {
        return 'site performance';
    }

    public function view(): string
    {
        return '@IntegratedDashboard/site_performance.html.twig';
    }

    /**
     * @throws MongoDBException
     * @throws GuzzleException
     */
    public function params(ChannelInterface $channel, User $user): array
    {
        $channelPerformance = $this->manager->getRepository(SitePerformance::class)
            ->findOneBy(
                ['channelID' => $channel->getId()],
                ['dateTime' => 'DESC']
            );
        if ($channelPerformance !== null) {
            $siteSpeed = $channelPerformance->getSiteSpeed() ?? "No speed found";
        } else {
            $siteSpeed = "No performance data found";
        }
        return [
            'siteSpeed' => $siteSpeed
        ];

    }
}
