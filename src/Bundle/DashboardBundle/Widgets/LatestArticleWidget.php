<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\HttpFoundation\Request;

class LatestArticleWidget implements WidgetInterface
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly DocumentManager $manager,
        private readonly int $amount
    ) {
        $this->id = 'latest_articles';
        $this->name = 'Latest articles';
        $this->view = '@IntegratedDashboard/latest_articles.html.twig';
    }
    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function view(): string
    {
        return $this->view;
    }

    public function params(ChannelInterface $channel, User $user, Request $request): array
    {
        $queryBuilder = $this->manager->createQueryBuilder(Article::class)
            ->field('channels.id')->equals($channel->getId())
            //->field('authors')->equals($user->getId())
            ->sort('publishTime.startDate', 'desc')
            ->limit($this->amount);
        $mostRecentArticles = $queryBuilder->getQuery()->execute();

        return [
            "widget" => $this,
            'mostRecentArticles' => $mostRecentArticles,
            'channel' => $channel,
        ];
    }
}
