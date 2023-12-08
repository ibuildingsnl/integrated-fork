<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\AnalyticsBundle\Document\AnalyticsData;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\DashboardBundle\Widgets\WidgetInterface;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class DeviceTypeWidget implements WidgetInterface
{

    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly DocumentManager $manager,
        private readonly string          $credential,
        private readonly LoggerInterface  $logger,
        private readonly BrandRepository  $brandRepository,
    )
    {
        $this->id = 'device_type';
        $this->name = 'Device type';
        $this->view = '@IntegratedDashboard/device_type.html.twig';
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
        $deviceType = $this->manager->getRepository(AnalyticsData::class)
            ->findOneBy(
                ['channelID' => $channel->getId(), 'dataType' => $this->id ],
                ['dateTime' => 'DESC']
            );
        $allDatas = $deviceType->getDatas();

        $maxElements = 10;
        foreach ($allDatas as &$dateRangeData) {
            if (count($dateRangeData) > $maxElements) {
                $dateRangeData = $this->processOtherDeviceType($dateRangeData, $maxElements-1);
            }
        }
        return [
            "widget" => $this,
            "deviceType" => $allDatas ?? [],
        ];
    }


    function processOtherDeviceType(array $dateRangeData, $maxElements): array
    {
        $otherDevices = 0;

        // Calcul du cumul des sessions et stockage des sources à partir du 10ème élément
        for ($i = $maxElements; $i < count($dateRangeData); $i++) {
            $otherDevices += $dateRangeData[$i]['amount'];
        }

        // Suppression des éléments à partir du 10ème
        array_splice($dateRangeData, $maxElements);

        // Ajout de l'élément "Other" avec le cumul des sessions
        $dateRangeData[] = [
            'device' => 'Other',
            'amount' => $otherDevices
        ];
        //dd($dateRangeData);

        return $dateRangeData;
    }
}
