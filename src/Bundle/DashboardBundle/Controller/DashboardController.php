<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\DashboardBundle\Controller;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Form\Type\BrandChoiceType;
use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\DashboardBundle\Document\WidgetConfig;
use Integrated\Bundle\DashboardBundle\Widgets\WidgetInterface;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use function Deployer\writeln;


class DashboardController extends AbstractController
{
    private array $widgets;

    public function __construct(
        private readonly ChannelContextInterface $channelContext,
        private readonly DocumentManager         $manager,
        private readonly ChannelRepository       $channelRepository,
        private readonly BrandRepository         $brandRepository,
        private readonly iterable                $allWidgets,
    )
    {
        /** @var WidgetInterface $widget */
        foreach ($this->allWidgets as $widget) {
            $this->widgets[$widget->getName()] = $widget;
        }
    }

    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $allBrands = $this->getBrands();
        $channel = $this->getChannel($request);
        $widgetAllData = $this->renderWidgets($channel, $user, $request);
        return $this->renderDashboardView($channel->getId(), $widgetAllData, $allBrands);
    }

    private function getBrands(): array
    {
        $allBrands = [];
        $brands = $this->brandRepository->All();
        foreach ($brands as $brand) {
            $channelId = $this->getChannelId($brand);
            $allBrands[] = [
                'id' => $brand->getId(),
                'name' => $brand->getName(),
                'channelId' => $channelId,
            ];
        }
        return $allBrands;
    }

    private function getChannelId($brand): string
    {
        $channelLinks = $brand->getChannelLinks();
        $channelId = null;
        /* @var $channelLink ChannelLink */
        foreach ($channelLinks as $channelLink)
        {
            $channel = $channelLink->channel;
            if ($channel->getType()->id == 'website') {
                $channelId = $channelLink->channel->getId();
            }
        }
        return $channelId;
    }

    public function getBrandForChannel(?ChannelInterface $channel): ?Brand
    {
        if ($channel instanceof ChannelInterface) {
            foreach ($this->brandRepository->all() as $brand) {
                if ($brand->hasChannel($channel)) {
                    return $brand;
                }
            }
        }
        return null;
    }

    private function getChannel($request): ChannelInterface
    {
        $selectedByFormBrand = $request->query->get('integrated_brand_choice');
       if ($selectedByFormBrand !== null) {
           $selectedBrand = $this->brandRepository->find($selectedByFormBrand);
           $channelId = $this->getChannelId($selectedBrand);
           $selectedChannel = $this->channelRepository->findOneBy(['id' => $channelId]);
        } else {
            $selectedChannel = $this->channelContext->getChannel();
        }
        return $selectedChannel ?? $this->channelRepository->findAll()[0];
    }

    /**
     * @throws MongoDBException
     */
    private function renderWidgets(ChannelInterface $channel, $user, Request $request): array    {
        $widgetAllData = [];

        $widgetConfigs = $this->manager->getRepository(WidgetConfig::class)
            ->createQueryBuilder()
            ->sort('order', 'asc')
            ->getQuery()
            ->execute();

        foreach ($widgetConfigs as $config) {
            $widget = $this->widgets[$config->getWidgetName()] ?? null;
            if ($widget) {
                $widgetAllData[] = [
                    'id' => $config->getWidgetId(),
                    'name' => $config->getWidgetName(),
                    'renderedView' => $this->renderView($widget->getView(), $widget->getParams($channel, $user, $request)),
                ];
            }
        }
        return $widgetAllData;
    }

    private function renderDashboardView(string $channelId, array $widgetAllData, array $allBrands): Response
    {
        return $this->render('@IntegratedDashboard/index.html.twig', [
            "channelId" => $channelId,
            "allBrands" => $allBrands,
            "widgetAllData" => $widgetAllData,
        ]);
    }
}
