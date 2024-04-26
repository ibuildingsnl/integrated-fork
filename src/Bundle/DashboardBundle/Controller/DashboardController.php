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
use Integrated\Common\Security\Resolver\PermissionResolver;
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
        $allowedBrands = $this->getBrands();
        $channel = $this->getChannel($request);
        $widgetAllData = $this->renderWidgets($channel, $user, $request);

        return $this->renderDashboardView(
            $channel->getId(),
            $widgetAllData,
            $allowedBrands
        );
    }

    private function getChannel($request): ChannelInterface
    {
        if ($selectedByFormBrand = $request->query->get('integrated_brand_choice')) {
            $selectedBrand = $this->brandRepository->find($selectedByFormBrand);
            $selectedChannel = $selectedBrand->getWebsiteChannel();
        } else {
            $selectedChannel = $this->channelContext->getChannel();
        }

        return $selectedChannel ?? $this->channelRepository->findAll()[0];
    }

    private function getBrands(): array
    {
        $allBrands = [];
        $brands = $this->getAllowedBrands();
        foreach ($brands as $brand) {
            if ($brand === null) {
                continue;
            }
            $channelId = $brand->getWebsiteChannel($brand);
            $allBrands[] = [
                'id' => $brand->getId(),
                'name' => $brand->getName(),
                'channelId' => $channelId,
            ];
        }
        return $allBrands;
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

    public function getAllowedBrands(): ?array
    {
        $channels = $this->manager->getRepository(Channel::class)->findBy([], ['name' => 1]);
        $user = $this->getUser();
        $allowedBrands = [];
        foreach ($channels as $channel) {
            $permissions = PermissionResolver::getPermissions($user, $channel->getPermissions());
            if (($permissions['read'] === true || $permissions['write'] === true) && $channel->getPrimaryDomain() != null) {
                $allowedBrands[] = $this->getBrandForChannel($channel);
            }
        }
        return $allowedBrands;
    }

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

    private function renderDashboardView(string $channelId, array $widgetAllData, array $allowedBrands): Response
    {
        return $this->render('@IntegratedDashboard/index.html.twig', [
            "channelId" => $channelId,
            "allBrands" => $allowedBrands,
            "widgetAllData" => $widgetAllData,
        ]);
    }
}
