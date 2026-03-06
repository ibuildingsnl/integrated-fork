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
    /** @var DocumentRepository<ContentType> */
    private DocumentRepository $contentTypeRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly QueryFactoryInterface $queryFactory,
        private readonly ClientInterface $solrClient,
        /** @var array<int, string> */
        private readonly array $allowedContentTypes,
    ) {
        $this->contentTypeRepository = $this->documentManager->getRepository(ContentType::class);
    }

    public function index(): Response
    {
        $user = $this->getUser();
        $allowedChannels = $user instanceof UserInterface ? $this->getAllowedChannels($user) : [];

        $channels = array_filter($allowedChannels, static function (Channel $channel): bool {
            return $channel->getPrimaryDomain() !== null && $channel->getPrimaryDomain() !== '';
        });

        $channels = array_map(static function (Channel $channel): array {
            return [
                'key' => $channel->getId(),
                'label' => str_replace(' Website', '', (string) $channel->getName()),
            ];
        }, $channels);

        $contentTypes = $this->contentTypeRepository->findAll();
        $contentTypes = array_map(static function (ContentType $contentType): array {
            return [
                'key' => $contentType->getId(),
                'label' => (string) $contentType->getName(),
            ];
        }, array_filter($contentTypes, function (ContentType $contentType): bool {
            return \in_array((string) $contentType->getClass(), $this->allowedContentTypes, true);
        }));

        return $this->render('@IntegratedContent/article_search/article_search.html.twig', [
            'channels' => json_encode(array_values($channels)) ?: '[]',
            'contentTypes' => json_encode(array_values($contentTypes)) ?: '[]',
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
            ]) ?: '{}',
        ]);
    }

    public function searchContentByChannel(Request $request, ?string $channelId = null): Response
    {
        $contentTypeIds = (string) $request->get('contentTypeIds', '');

        if ($channelId === null) {
            return new Response(
                json_encode(['msg' => 'No channel id specified']) ?: '{}',
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        $q = (string) $request->get('term', '');

        if (empty($q)) {
            return new Response(
                json_encode(['msg' => 'No search term specified']) ?: '{}',
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        /** @var Channel|null $channel */
        $channel = $this->documentManager->getRepository(Channel::class)->find($channelId);
        if (!$channel instanceof Channel) {
            return new Response(
                json_encode(['msg' => 'Channel not found']) ?: '{}',
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => 'application/json']
            );
        }

        $criteria = [
            'contenttypes' => explode(',', $contentTypeIds),
            'channels' => explode(',', $channelId),
            'sort' => 'rel',
            'q' => $q,
        ];

        $contentTypes = explode(',', $contentTypeIds);

        if (\in_array('image', $contentTypes) || \in_array('file', $contentTypes)) {
            $criteria['channels'] = [];
        }

        $query = $this->queryFactory
            ->createQuery(IntegratedContent::class, $criteria)
            ->getQuery();

        /** @var Document[] $items */
        $items = $this->solrClient->select($query)->getDocuments();

        $ret = array_map(
            /**
             * @throws \Exception
             */
            function (Document $contentItem) use ($channel): array {
                $content = $contentItem['content'] ?? '';
                $content = \is_array($content) ? implode('', $content) : (string) $content;
                $content = substr(strip_tags($content), 0, 255) ?: '';

                $file = $contentItem['file'] ?? null;
                if (\is_string($file) && $file !== '') {
                    $fileData = json_decode($file, true); // Decode to an associative array
                    $url = \is_array($fileData) ? ($fileData['pathname'] ?? null) : null;
                } else {
                    $url = $contentItem['url_'.$channel->getId()] ?? null;
                }

                return [
                    'id' => (string) ($contentItem['type_id'] ?? ''),
                    'title' => (string) ($contentItem['title'] ?? ''),
                    'subtitle' => ucfirst((string) ($contentItem['type_name'] ?? '')).' | '.
                    (new \DateTimeImmutable((string) ($contentItem['pub_time'] ?? 'now')))->format('d-m-Y'),
                    'text' => $content,
                    'url' => $url,
                ];
            },
            $items
        );

        $ret = array_map(function (array $contentItem) use ($channel): array {
            return array_merge($contentItem, [
                'url' => 'https://'.$channel->getPrimaryDomain().(string) ($contentItem['url'] ?? ''),
            ]);
        }, $ret);

        if (\count($ret) > 0) {
            return new Response(json_encode($ret) ?: '[]', headers: ['Content-Type' => 'application/json']);
        } else {
            return new Response(
                json_encode([
                    'term' => $q,
                    'channelId' => $channelId,
                    'contentTypeId' => $contentTypeIds,
                ]) ?: '{}',
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
        /** @var Channel[] $channels */
        $channels = $this->documentManager->getRepository(Channel::class)->findBy([], ['name' => 'asc']);
        $allowed = [];

        foreach ($channels as $channel) {
            $channelPermissions = $channel->getPermissions();
            $permissions = PermissionResolver::getPermissions(
                $user,
                \is_array($channelPermissions) ? array_values($channelPermissions) : iterator_to_array($channelPermissions)
            );

            if ($permissions['read'] === true || $permissions['write'] === true) {
                $allowed[] = $channel;
            }
        }

        return $allowed;
    }
}
