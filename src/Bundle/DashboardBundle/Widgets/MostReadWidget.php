<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Document\AnalyticsData;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\HttpFoundation\Request;

class MostReadWidget implements WidgetInterface
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly DocumentManager $manager,
        private readonly int $limit,
    ) {
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
        $mostReadArticle = $this->manager->getRepository(AnalyticsData::class)
            ->findOneBy(
                ['channelID' => $channel->getId(), 'dataType' => $this->id],
                ['dateTime' => 'DESC']
            );
        $allDatas = $mostReadArticle->getData();
        $slicedDatas = [];
        foreach ($allDatas as $key => $values) {
            $filteredValues = array_filter($values, function ($element) {
                return $element['slug'] !== '';
            });

            $slicedValues = \array_slice($filteredValues, 0, $this->limit);
            $slicedDatas[$key] = array_values($slicedValues);

            foreach ($slicedDatas[$key] as &$element) { // Utilisation de "&" pour obtenir une référence à chaque élément
                $article = $this->manager
                    ->getRepository(Article::class)
                    ->findOneBy([
                        'slug' => $element['slug'],
                        'channels.id' => $channel->getId(),
                    ]);

                if ($article !== null) {
                    $element['id'] = $article->getId();
                } else {
                    $element['id'] = null;
                }
            }
            unset($element); // Dissocier la référence de la dernière itération
        }
        // dd($slicedDatas);
        return [
            'widget' => $this,
            'mostReadArticles' => $slicedDatas,
        ];
    }
}
