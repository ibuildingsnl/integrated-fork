<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Security\Resolver\PermissionResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleSearchController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
    }

    public function index() {
        $channels = array_filter($this->getAllowedChannels($this->getUser()), function($channel) {
            return $channel->getPrimaryDomain() !== null && strlen($channel->getPrimaryDomain()) > 0;
        });

        $channels = array_map(function($channel) {
            return [
                'key' => $channel->getId(),
                'label' => $channel->getName(),
            ];
        }, $channels);

        return $this->render('@IntegratedContent/article_search/article_search.html.twig', [
            'channels' => json_encode(array_values($channels)),
        ]);
    }

    public function search(Request $request) {
        $test = new \stdClass();
        $test->msg = 'Hello';
        $test->rand = rand(0, 1000);
        $test->term = $request->query->get('term');

        return $this->json($test);
    }

    public function searchContentByChannel(Request $request, ?string $channelId = null, ?string $contentTypeIds = null)
    {
        if($channelId === null) {
            return new Response(
                json_encode([
                    'msg' => 'No channel id specified',
                ]),
                status: Response::HTTP_BAD_REQUEST,
                headers: ['Content-Type' => 'application/json']
            );
        }

        $contentTypeIds = 'news,article,file';

        $channelQueryValue = self::formatQueryValue($channelId);
        $contentTypeQueryValue = self::formatQueryValue($contentTypeIds);

        $query = $this->getSolarium()->createSelect();
        $query->createFilterQuery('channels')
            ->addTag('channels')
            ->setQuery('facet_channels: ' . $channelQueryValue);

        $query->createFilterQuery('contenttypes')
              ->setQuery('type_name: ' . $contentTypeQueryValue);

        if ($q = $request->get('term')) {
            $edismax = $query->getEDisMax();
            $edismax->setQueryFields('title content');
            $edismax->setMinimumMatch('75%');

            $query->setQuery($q);
        }

        // Ensure the item has a non-empty 'url_vleesmagazine' field

        $result = $this->getSolarium()->select($query);
        $contentItems = $result->getDocuments();

        if ($contentItems) {
            return new Response(json_encode($contentItems), headers: ['Content-Type' => 'application/json']);
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


    function formatQueryValue($ids) {
        $idArray = explode(',', $ids);

        if (count($idArray) == 1) {
            $queryValue = '("' . $idArray[0] . '")';
        } else {
            $formattedIds = array_map(function($id) {
                return '"' . $id . '"';
            }, $idArray);

            $queryValue = '(' . implode(' OR ', $formattedIds) . ')';
        }

        return $queryValue;
    }
}
