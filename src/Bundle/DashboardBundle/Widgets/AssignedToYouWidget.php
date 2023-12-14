<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Solarium\Client as solariumClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use DateTimeImmutable;


class AssignedToYouWidget implements WidgetInterface
{

    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly DocumentManager            $manager,
        private readonly solariumClient             $solariumClient,
        private readonly UrlGeneratorInterface      $urlGenerator,
    )
    {
        $this->id = 'assigned_to_you';
        $this->name = 'Assigned To You';
        $this->view = '@IntegratedDashboard/assigned_to_you.html.twig';
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
        /** @var $client \Solarium\Client */
        //
        // Get documents assigned to this user
        //
        $query = $this->solariumClient->createSelect();

        $assignedContent = [];

        $userId = $user->getId();
        $query
            ->createFilterQuery('workflow_assigned_id')
            ->setQuery('facet_workflow_assigned_id:' . $userId . '');

        $result = $this->solariumClient->select($query);
        $assignedContent = $result->getDocuments();
        $assignedToYou = [];

        foreach ($assignedContent as $solarArticle) {
            $article = $this->manager->getRepository(Article::class)
                ->findOneBy(
                    ['id' => $solarArticle->type_id]
                );
            if ($article->getTitle() == "Technologie: l'essort de windows Millenium")
            {
                //dd($solarArticle);
            }
            $assignedToYou[] = [
                'id' => $article->getId(),
                'title' =>$article->getTitle(),
                'slug' =>$article->getSlug(),
                'last_changes' =>$article->getUpdatedAt(),
                'path' => $this->urlGenerator->generate('integrated_content_content_edit', ['id' => $article->getId()]),
            ];
        }
        usort($assignedToYou, array($this, 'compareLastChanges'));
        return [
            "widget" => $this,
            'assignedToYou' => $assignedToYou,
            'channel' => $channel,
        ];
    }
    /**
     * @throws \Exception
     */
    function compareLastChanges($a, $b) {
        $dateA = $a['last_changes'];
        $dateB = $b['last_changes'];

        if ($dateA == $dateB) {
            return 0;
        }

        return ($dateA > $dateB) ? -1 : 1;
    }
}
