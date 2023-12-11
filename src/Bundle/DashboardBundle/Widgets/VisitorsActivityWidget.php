<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Document\AnalyticsData;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\DashboardBundle\Widgets\WidgetInterface;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use DateTimeImmutable;

class VisitorsActivityWidget implements WidgetInterface
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly string          $credential,
        private readonly LoggerInterface $logger,
        private readonly BrandRepository $brandRepository,
        private readonly ObjectRepository $channelRepository,
        private readonly DocumentManager  $manager,
    ){
        $this->id = 'visitors_activity';
        $this->name = 'Visitors activity';
        $this->view = '@IntegratedDashboard/visitors_activity.html.twig';
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

    /**
     * @throws GuzzleException
     */
    public function getParams(ChannelInterface $channel, User $user, Request $request): array
    {
        $deviceType = $this->manager->getRepository(AnalyticsData::class)
            ->findOneBy(
                ['channelID' => $channel->getId(), 'dataType' => $this->id ],
                ['dateTime' => 'DESC']
            );
        $allDatas = $deviceType->getDatas();

        return [
            "widget" => $this,
            "userActivityByDate" => $allDatas['visitorsActivity'] ?? [],
        ];
    }
}
