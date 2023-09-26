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
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Component\HttpFoundation\Response;


class DashboardController extends AbstractController
{

    public function __construct(private readonly ChannelContextInterface $channelContext){}

    public function index(string $searchSelection = 'all'): Response
    {
            return $this->render('@IntegratedDashboard/index.html.twig',[
            'controller_name' => 'DashboardController',
            "channel" => $this->channelContext->getChannel()->getName()
        ]);

    }
}
