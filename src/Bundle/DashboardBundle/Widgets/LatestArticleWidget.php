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
        private readonly int $limit
    ) {
        $this->id = 'latest_articles';
        $this->name = 'Latest articles';
        $this->view = '@IntegratedDashboard/latest_articles.html.twig';
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
        $currentDateTime = new \DateTimeImmutable();

        $queryBuilder = $this->manager->createQueryBuilder(Article::class)
            ->field('channels.id')->equals($channel->getId())
            ->field('publishTime.startDate')->lte($currentDateTime)
            ->sort('publishTime.startDate', 'desc')
            ->limit($this->limit);

        $mostRecentArticles = $queryBuilder->getQuery()->execute();

        $result = [
            "widget" => $this,
            'mostRecentArticles' => $mostRecentArticles,
        ];
        return $result;
    }

}
