<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Document\AnalyticsData;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;


class MostReadWidget implements WidgetInterface
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly DocumentManager $manager,
        private readonly int             $limit,
    )
    {
        $this->id = 'most_read';
        $this->name = 'Most read';
        $this->view = '@IntegratedDashboard/most_read.html.twig';
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

    /**
     * @throws GuzzleException
     */

    public function getParams(ChannelInterface $channel, User $user, Request $request): array
    {

        $deviceType = $this->manager->getRepository(AnalyticsData::class)
            ->findOneBy(
                ['channelID' => $channel->getId(), 'dataType' => $this->id ],
                ['dateTime' => 'DESC']
            );
        $allDatas = $deviceType->getDatas();

        foreach ($allDatas as $key => $values) {
            $slicedDatas[$key] = array_slice($values, 0, $this->limit);
        }
        return [
            "widget" => $this,
            "mostReadArticles" => $slicedDatas,
        ];
    }

    public function getDataFromDB($channel)
    {
        $views = [
            'weekly' => 'weeklyViews',
            'monthly' => 'monthlyViews',
            'quarterly' => 'quarterlyViews',
            'semester' => 'semesterViews',
            'yearly' => 'yearlyViews',
        ];

        $mostReadArticles = [];

        foreach ($views as $viewKey => $viewValue) {
            $articlesDB = $this->manager->createQueryBuilder(Article::class)
                ->field('channels.id')->equals($channel->getId())
                ->field("metadata.data.$viewValue")
                ->sort("metadata.data.$viewValue", 'desc')
                ->limit($this->limit)
                ->getQuery()
                ->execute();

            $articles = [];

            foreach ($articlesDB as $article) {
                $articles[] = [
                    'id' => $article->getId(),
                    'slug' => $article->getSlug(),
                    'title' => $article->getTitle(),
                    'views' => $article->getMetadata()->get($viewValue),
                ];
            }

            $mostReadArticles["{$viewKey}MostReadArticles"] = $articles;
        }

        return $mostReadArticles;
    }

}


