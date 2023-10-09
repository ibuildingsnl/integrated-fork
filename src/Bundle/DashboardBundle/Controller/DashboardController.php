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

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\DashboardBundle\Document\WidgetConfig;
use Integrated\Bundle\DashboardBundle\Widgets\WidgetInterface;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractController
{
    private array $widgets;
    public function __construct(
        private readonly ChannelContextInterface $channelContext,
        private readonly DocumentManager $manager,
        private readonly iterable $allWidgets,
    ) {
        /** @var WidgetInterface $widget */
        foreach ($this->allWidgets as $widget) {
            $this->widgets[$widget->name()] = $widget;
        }
    }

    public function index(Request $request): Response
    {
        $user = $this->getUser();

        $selectChannelForm = $this->createForm(ChannelChoiceType::class, null, [
            'multiple' => false,
            'return_object' => true, // true = object, false = ID
        ]);
        $selectedByFormChannel = $request->query->get('integrated_channel_choice');
        $channel = $this->manager->getRepository(Channel::class)->find($selectedByFormChannel) ?? $this->channelContext->getChannel();
        $renderedWidgets = [];
        foreach ($this->manager->getRepository(WidgetConfig::class)->findAll() as $config) {
            /** @var WidgetInterface $widget */
            $widget = $this->widgets[$config->getWidgetName()] ?? null;
            if (!$widget) {
                continue;
            }
            $renderedWidgets[] = $this->renderView($widget->view(), $widget->params($channel,$user));
        }
        //dd($renderedWidgets);

        // Render the view with the data
        return $this->render('@IntegratedDashboard/index.html.twig', [
            "channelName" => $channel->getName(),
            'widgets' => $renderedWidgets,
            "channelForm" => $selectChannelForm->createView(),
        ]);
    }


}
