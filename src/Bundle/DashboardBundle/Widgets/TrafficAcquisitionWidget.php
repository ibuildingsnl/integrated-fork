<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\AnalyticsBundle\Document\AnalyticsData;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use DateTimeImmutable;

class TrafficAcquisitionWidget implements WidgetInterface
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly DocumentManager $manager,
    )
    {
        $this->id = 'traffic_acquisition';
        $this->name = 'Traffic acquisition';
        $this->view = '@IntegratedDashboard/traffic_acquisition.html.twig';
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

        $trafficAcquisition = $this->manager->getRepository(AnalyticsData::class)
            ->findOneBy(
                ['channelID' => $channel->getId(), 'dataType' => $this->id ],
                ['dateTime' => 'DESC']
            );
        $allDatas = $trafficAcquisition->getDatas();

        $maxElements = 10;
        foreach ($allDatas as &$dateRangeData) {
            if (count($dateRangeData) > $maxElements) {
                $dateRangeData = $this->processOtherTrafficAcquisition($dateRangeData, $maxElements-1);
            }
        }

        return [
            "widget" => $this,
            "trafficAcquisition" => $allDatas,
        ];
    }


    function processOtherTrafficAcquisition(array $trafficAcquisition, $maxElements): array
    {
        $otherSessions = 0;

        for ($i = $maxElements; $i < count($trafficAcquisition); $i++) {
            $otherSessions += $trafficAcquisition[$i]['sessions'];
        }

        array_splice($trafficAcquisition, $maxElements);

        $trafficAcquisition[] = [
            'source' => 'Other',
            'sessions' => $otherSessions
        ];

        return $trafficAcquisition;
    }
}
