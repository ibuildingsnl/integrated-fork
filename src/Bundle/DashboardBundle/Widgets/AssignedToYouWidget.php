<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Solarium\Client as solariumClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AssignedToYouWidget implements WidgetInterface
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly DocumentManager $manager,
        private readonly solariumClient $solariumClient,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
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
        $assignedToYou = $this->getAssignedElement($user, $channel);
        usort($assignedToYou, [$this, 'compareLastChanges']);

        return [
            'widget' => $this,
            'assignedToYou' => $assignedToYou,
            'channel' => $channel,
        ];
    }

    public function getAssignedElement(User $user, ChannelInterface $channel): ?array
    {
        $query = $this->solariumClient->createSelect();
        $userId = $user->getId();
        $query->createFilterQuery('workflow_assigned_id')
              ->setQuery('facet_workflow_assigned_id:'.$userId.'');
        $query->createFilterQuery('pub_not_active')
              ->setQuery('-pub_active:true');

        $result = $this->solariumClient->select($query);
        $assignedContent = $result->getDocuments();
        $assignedElement = [];

        foreach ($assignedContent as $solarArticle) {
            $workflow_deadline = \DateTimeImmutable::createFromFormat('d-m-Y', $solarArticle->workflow_deadline);
            if (!$workflow_deadline instanceof \DateTimeImmutable) {
                $workflow_deadline = null;
            }

            $article = $this->manager->getRepository(Article::class)
                ->findOneBy(
                    ['id' => $solarArticle->type_id]
                );

            if (!\in_array($channel, $article->getChannels())) {
                continue;
            }

            $assignedElement[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'slug' => $article->getSlug(),
                'last_changes' => $article->getUpdatedAt(),
                'path' => $this->urlGenerator->generate('integrated_content_content_edit', ['id' => $article->getId()]),
                'workflow_deadline' => $workflow_deadline,
                'workflow_color_string' => $solarArticle->workflow_color_string ?? null,
                'workflow_icon_string' => $solarArticle->workflow_icon_string ?? null,
            ];
        }

        return $assignedElement;
    }

    /**
     * @throws \Exception
     */
    public function compareLastChanges($a, $b)
    {
        $dateA = $a['last_changes'];
        $dateB = $b['last_changes'];

        if ($dateA == $dateB) {
            return 0;
        }

        return ($dateA > $dateB) ? -1 : 1;
    }
}
