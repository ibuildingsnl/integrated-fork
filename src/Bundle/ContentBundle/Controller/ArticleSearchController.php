<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Security\Resolver\PermissionResolver;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Solarium\Core\Client\ClientInterface;
use Solarium\QueryType\Select\Result\Document;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleSearchController extends AbstractController
{
    private DocumentRepository $contentTypeRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly QueryFactoryInterface $queryFactory,
        private readonly ClientInterface $solrClient,
        private readonly array $allowedContentTypes,
    ) {
        $this->contentTypeRepository = $this->documentManager->getRepository(ContentType::class);
    }

    public function index()
    {
        $channels = array_filter($this->getAllowedChannels($this->getUser()), function ($channel) {
            return $channel->getPrimaryDomain() !== null && $channel->getPrimaryDomain() !== '';
        });

        $channels = array_map(function ($channel) {
            return [
                'key' => $channel->getId(),
                'label' => str_replace(' Website', '', $channel->getName()),
            ];
        }, $channels);

        $contentTypes = $this->contentTypeRepository->findAll();
        $contentTypes = array_map(function ($contentType) {
            return [
                'key' => $contentType->getId(),
                'label' => $contentType->getName(),
            ];
        }, array_filter($contentTypes, function ($contentType) {
            return \in_array($contentType->getClass(), $this->allowedContentTypes);
        }));

        return $this->render('@IntegratedContent/article_search/article_search.html.twig', [
            'channels' => json_encode(array_values($channels)),
            'contentTypes' => json_encode(array_values($contentTypes)),
            'translations' => json_encode([
                'apply' => $this->getTranslator()->trans('Apply'),
                'cancel' => $this->getTranslator()->trans('Cancel'),
                'no_results' => $this->getTranslator()->trans('No results'),
                'new_tab' => $this->getTranslator()->trans('Open in new tab'),
                'require_searchterm' => $this->getTranslator()->trans('Enter a search term to begin searching'),
                'require_linktext' => $this->getTranslator()->trans('Please fill in the link text'),
                'searching' => $this->getTranslator()->trans('Searching...'),
                'select_channel' => $this->getTranslator()->trans('Select a channel'),
                'channels' => $this->getTranslator()->trans('Channels'),
                'content_types' => $this->getTranslator()->trans('Content types'),
                'link_text' => $this->getTranslator()->trans('Link text'),
                'link_title' => $this->getTranslator()->trans('Link title'),
                'url_or_searchterm' => $this->getTranslator()->trans('URL or search term'),
                'ready' => $this->getTranslator()->trans('Good to go!'),
            ]),
        ]);
    }

    public function searchContentByChannel(Request $request, ?string $channelId = null)
    {
        $contentTypeIds = $request->get('contentTypeIds', '');

        if ($channelId === null) {
            return new Response(
                json_encode(['msg' => 'No channel id specified']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        $q = $request->get('term');

        if (empty($q)) {
            return new Response(
                json_encode(['msg' => 'No search term specified']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        /** @var Channel $channel */
        $channel = $this->documentManager->getRepository(Channel::class)->find($channelId);

        $query = $this->queryFactory
            ->createQuery(IntegratedContent::class, [
                'contenttypes' => explode(',', $contentTypeIds),
                'channels' => explode(',', $channelId),
                'sort' => 'rel',
                'q' => $q,
            ])
            ->getQuery();

        /** @var Document[] $items */
        $items = $this->solrClient->select($query)->getDocuments();
        $contentIds = [];

        $ret = array_map(
            /**
             * @throws \Exception
             */
            function ($contentItem) use ($channel, &$contentIds) {
                $contentIds[] = $contentItem->type_id;
                $content = \is_array($contentItem->content) ? implode('', $contentItem->content) : $contentItem->content;
                $content = substr(strip_tags($content), 0, 255);

                if ($contentItem->file) {
                    $fileData = json_decode($contentItem->file, true); // Decode to an associative array
                    $url = $fileData['pathname'] ?? null; // Access the 'pathname' key
                } else {
                    $url = $contentItem['url_'.$channel->getId()];
                }

                return [
                    'id' => $contentItem->type_id,
                    'title' => $contentItem->title,
                    'subtitle' => ucfirst($contentItem->type_name).' | '.
                    (new \DateTimeImmutable($contentItem->pub_time))->format('d-m-Y'),
                    'text' => $content,
                    'url' => $url,
                ];
            },
            $items
        );

        $ret = array_map(function ($contentItem) use ($channel) {
            return array_merge($contentItem, [
                'url' => 'https://'.$channel->getPrimaryDomain().$contentItem['url'],
            ]);
        }, $ret);

        if (\count($ret) > 0) {
            return new Response(json_encode($ret), headers: ['Content-Type' => 'application/json']);
        } else {
            return new Response(
                json_encode([
                                'term' => $q,
                                'channelId' => $channelId,
                                'contentTypeId' => $contentTypeIds,
                            ]),
                status: Response::HTTP_NOT_FOUND,
                headers: ['Content-Type' => 'application/json']
            );
        }
    }

    /**
     * @return Channel[]
     */
    private function getAllowedChannels(UserInterface $user): array
    {
        $channels = $this->documentManager->getRepository(Channel::class)->findBy([], ['name' => 1]);
        $allowed = [];

        foreach ($channels as $channel) {
            $permissions = PermissionResolver::getPermissions($user, $channel->getPermissions());

            if ($permissions['read'] === true || $permissions['write'] === true) {
                $allowed[] = $channel;
            }
        }

        return $allowed;
    }
}
