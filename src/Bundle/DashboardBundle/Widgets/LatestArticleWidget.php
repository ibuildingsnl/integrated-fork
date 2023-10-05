<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Common\Channel\ChannelInterface;

class LatestArticleWidget implements WidgetInterface
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly int $amount
    ) {
    }

    public function name(): string
    {
        return 'latest articles';
    }

    public function view(): string
    {
        return '@IntegratedDashboard/latest_articles_widget.html.twig';
    }

    public function params(ChannelInterface $channel): array
    {
        $queryBuilder = $this->manager->createQueryBuilder(Article::class)
            ->field('channels.id')->equals($channel->getId())
            ->sort('publishTime.startDate', 'desc')
            ->limit($this->amount);
        $mostRecentArticles = $queryBuilder->getQuery()->execute();

        return [
            'mostRecentArticles' => $mostRecentArticles,
            'channel' => $channel,
        ];
    }
}
