<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Security\Resolver\PermissionResolver;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Solarium\Core\Client\ClientInterface;
use Solarium\QueryType\Select\Result\Document;

class ArticleSearchService implements ArticleSearchServiceInterface
{
    /** @var DocumentRepository<Channel> */
    private DocumentRepository $channelRepository;

    /** @var DocumentRepository<ContentType> */
    private DocumentRepository $contentTypeRepository;

    /**
     * @param array<int, string> $allowedContentTypes
     */
    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly QueryFactoryInterface $queryFactory,
        private readonly ClientInterface $solrClient,
        private readonly array $allowedContentTypes,
    ) {
        $this->channelRepository = $this->documentManager->getRepository(Channel::class);
        $this->contentTypeRepository = $this->documentManager->getRepository(ContentType::class);
    }

    public function findChannel(string $channelId): ?Channel
    {
        $channel = $this->channelRepository->find($channelId);

        return $channel instanceof Channel ? $channel : null;
    }

    public function getAvailableChannels(?UserInterface $user): array
    {
        if (!$user instanceof UserInterface) {
            return [];
        }

        $channels = array_filter($this->getAllowedChannels($user), static function (Channel $channel): bool {
            return $channel->getPrimaryDomain() !== null && $channel->getPrimaryDomain() !== '';
        });

        return array_values(array_map(static function (Channel $channel): array {
            return [
                'key' => $channel->getId(),
                'label' => str_replace(' Website', '', (string) $channel->getName()),
            ];
        }, $channels));
    }

    public function getAvailableContentTypes(): array
    {
        $contentTypes = $this->contentTypeRepository->findAll();

        $contentTypes = array_filter($contentTypes, function (ContentType $contentType): bool {
            return \in_array((string) $contentType->getClass(), $this->allowedContentTypes, true);
        });

        return array_values(array_map(static function (ContentType $contentType): array {
            return [
                'key' => (string) $contentType->getId(),
                'label' => (string) $contentType->getName(),
            ];
        }, $contentTypes));
    }

    public function searchInChannel(Channel $channel, string $term, array $contentTypeIds): array
    {
        $criteria = [
            'contenttypes' => $contentTypeIds,
            'channels' => [$channel->getId()],
            'sort' => 'rel',
            'q' => $term,
        ];

        if (\in_array('image', $contentTypeIds, true) || \in_array('file', $contentTypeIds, true)) {
            $criteria['channels'] = [];
        }

        $query = $this->queryFactory
            ->createQuery(IntegratedContent::class, $criteria)
            ->getQuery();

        /** @var Document[] $items */
        $items = $this->solrClient->select($query)->getDocuments();

        $results = array_map(
            static function (Document $contentItem) use ($channel): array {
                $content = $contentItem['content'] ?? '';
                $content = \is_array($content) ? implode('', $content) : (string) $content;
                $content = substr(strip_tags($content), 0, 255) ?: '';

                $url = '';
                $file = $contentItem['file'] ?? null;

                if (\is_string($file) && $file !== '') {
                    $fileData = json_decode($file, true);
                    $url = \is_array($fileData) ? (string) ($fileData['pathname'] ?? '') : '';
                }

                if ($url === '') {
                    $url = (string) ($contentItem['url_'.$channel->getId()] ?? '');
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

        $domain = (string) $channel->getPrimaryDomain();

        return array_map(static function (array $contentItem) use ($domain): array {
            return array_merge($contentItem, [
                'url' => 'https://'.$domain.(string) ($contentItem['url'] ?? ''),
            ]);
        }, $results);
    }

    /**
     * @return Channel[]
     */
    private function getAllowedChannels(UserInterface $user): array
    {
        /** @var Channel[] $channels */
        $channels = $this->channelRepository->findBy([], ['name' => 'asc']);
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
