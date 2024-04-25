<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Security\Resolver\PermissionResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleSearchController extends AbstractController
{
    private DocumentRepository $contentTypeRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
    ) {
        $this->contentTypeRepository = $this->documentManager->getRepository(ContentType::class);
    }

    public function index() {
        $allowedContentTypeIds = [
            'article',
            'image',
            'file',
        ];

        $channels = array_filter($this->getAllowedChannels($this->getUser()), function ($channel) {
            return $channel->getPrimaryDomain() !== null && strlen($channel->getPrimaryDomain()) > 0;
        });

        $channels = array_map(function ($channel) {
            return [
                'key' => $channel->getId(),
                'label' => $channel->getName(),
            ];
        }, $channels);

        $contentTypes = $this->contentTypeRepository->findAll();
        $contentTypes = array_map(function ($contentType) {
            return [
                'key' => $contentType->getId(),
                'label' => $contentType->getName(),
            ];
        }, array_filter($contentTypes, function($contentType) use ($allowedContentTypeIds) {
            return in_array($contentType->getId(), $allowedContentTypeIds);
        }));

        return $this->render('@IntegratedContent/article_search/article_search.html.twig', [
            'channels' => json_encode(array_values($channels)),
            'contentTypes' => json_encode(array_values($contentTypes)),
        ]);
    }

    public function searchContentByChannel(Request $request, ?string $channelId = null) {
        $contentTypeIds = $request->get('contentTypeIds', '');

        if ($channelId === null) {
            return new Response(
                json_encode(['msg' => 'No channel id specified']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        if (count($contentTypeIds) === 0) {
            return new Response(
                json_encode(['msg' => 'No content type id(s) specified']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        $q = $request->get('term');

        if(empty($q) || strlen($q) === 0) {
            return new Response(
                json_encode(['msg' => 'No search term specified']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        $contentRepository = $this->documentManager->getRepository(Content::class);
        /** @var Channel $channel */
        $channel = $this->documentManager->getRepository(Channel::class)->find($channelId);

        $channelQueryValue = self::formatQueryValue($channelId);
        $contentTypeQueryValue = self::formatQueryValue($contentTypeIds);

        $query = $this->getSolarium()->createSelect();
        $query->createFilterQuery('channels')
              ->addTag('channels')
              ->setQuery('facet_channels: ' . $channelQueryValue);

        $query->createFilterQuery('contenttypes')
              ->setQuery('type_name: ' . $contentTypeQueryValue);

        $edismax = $query->getEDisMax();
        $edismax->setQueryFields('title content');
        $edismax->setMinimumMatch('75%');

        $query->setQuery($q);

        $result = $this->getSolarium()->select($query);
        $contentItems = $result->getDocuments();
        $contentIds = [];

        $ret = array_map(function ($contentItem) use ($channel, &$contentIds) {
            $contentIds[] = $contentItem->type_id;

            return [
                'id' => $contentItem->type_id,
                'title' => $contentItem->title,
                'subtitle' => ucfirst($contentItem->type_name) . ' | ' . (new \DateTimeImmutable(
                        $contentItem->pub_time
                    ))->format('d-m-Y'),
                'text' => substr(strip_tags(implode('', $contentItem->content)), 0, 255),
                'url' => $contentItem['url_' . $channel->getId()],
            ];
        }, $contentItems);

        $ret = array_map(function ($contentItem) use ($channel) {
            return array_merge($contentItem, [
                'url' => $channel->getPrimaryDomain() . $contentItem['url'],
            ]);
        }, $ret);

        if (count($ret) > 0) {
            return new Response(json_encode($ret), headers: ['Content-Type' => 'application/json']);
        } else {
            return new Response(
                         json_encode([
                                         'term' => $q,
                                         'channelId' => $channelId,
                                         'contentTypeId' => $contentTypeIds,
                                     ]),
                status:  Response::HTTP_NOT_FOUND,
                headers: ['Content-Type' => 'application/json']
            );
        }
    }

    /**
     * @return Channel[]
     */
    private function getAllowedChannels(UserInterface $user): array {
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

    function formatQueryValue($ids) {
        $idArray = explode(',', $ids);

        if (count($idArray) == 1) {
            $queryValue = '("' . $idArray[0] . '")';
        } else {
            $formattedIds = array_map(function ($id) {
                return '"' . $id . '"';
            }, $idArray);

            $queryValue = '(' . implode(' OR ', $formattedIds) . ')';
        }

        return $queryValue;
    }
}
