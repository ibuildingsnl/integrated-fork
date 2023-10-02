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
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Component\HttpFoundation\Response;
use \Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;


class DashboardController extends AbstractController
{

    public function __construct(private readonly ChannelContextInterface $channelContext, private readonly DocumentManager $manager)
    {
    }

    public function index(Request $request): Response
    {
        $selectChannelForm = $this->createForm(ChannelChoiceType::class, null, [
            'multiple' => false,
            'return_object' => true, // true = object, false = ID
        ]);
        $selectedByFormChannel = $request->query->get('integrated_channel_choice');
        $objectChannel = $this->manager->getRepository(Channel::class)->find($selectedByFormChannel);
        $objectChannel !== null ? $this->channelContext->setChannel($objectChannel) : null;
        $channelName = $this->channelContext->getChannel()->getId();


        $queryBuilder = $this->manager->createQueryBuilder(Article::class)
            ->field('channels.id')->equals($channelName)
            ->sort('publishTime.startDate', 'desc')
            ->limit(10);

        $mostRecentArticles = $queryBuilder->getQuery()->execute();

        // Render the view with the data
        return $this->render('@IntegratedDashboard/index.html.twig', [
            "channelName" => $this->channelContext->getChannel()->getName(),
            "mostRecentArticles" => $mostRecentArticles,
            "nbrOfArticles" => count($mostRecentArticles),
            "channelForm" => $selectChannelForm->createView(),
        ]);
    }
}
