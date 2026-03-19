<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Integrated\Bundle\ContentBundle\Services\ArticleSearchServiceInterface;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleSearchController extends AbstractController
{
    public function __construct(
        private readonly ArticleSearchServiceInterface $articleSearchService,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $channels = $this->articleSearchService->getAvailableChannels($user instanceof UserInterface ? $user : null);
        $contentTypes = $this->articleSearchService->getAvailableContentTypes();
        $searchData = [];

        $rawData = (string) $request->query->get('data', '');

        if ($rawData !== '') {
            $decoded = json_decode($rawData, true);

            if (\is_array($decoded)) {
                $searchData = $decoded;
            }
        }

        return $this->render('@IntegratedContent/article_search/article_search.html.twig', [
            'channels' => $channels,
            'contentTypes' => $contentTypes,
            'searchData' => $searchData,
            'translations' => [
                'apply' => $this->getTranslator()->trans('Apply'),
                'cancel' => $this->getTranslator()->trans('Cancel'),
                'no_results' => $this->getTranslator()->trans('No results'),
                'new_tab' => $this->getTranslator()->trans('Open in new tab'),
                'require_searchterm' => $this->getTranslator()->trans('Enter a search term to begin searching'),
                'require_link_text' => $this->getTranslator()->trans('Please fill in the link text'),
                'require_link_title' => $this->getTranslator()->trans('Please fill in the link title'),
                'searching' => $this->getTranslator()->trans('Searching...'),
                'select_channel' => $this->getTranslator()->trans('Select a channel'),
                'channels' => $this->getTranslator()->trans('Channels'),
                'content_types' => $this->getTranslator()->trans('Content types'),
                'link_text' => $this->getTranslator()->trans('Link text'),
                'link_title' => $this->getTranslator()->trans('Link title'),
                'url_or_searchterm' => $this->getTranslator()->trans('URL or search term'),
                'ready' => $this->getTranslator()->trans('Good to go!'),
            ],
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

        $channel = $this->articleSearchService->findChannel($channelId);

        if ($channel === null) {
            return new Response(
                json_encode(['msg' => 'Channel not found']) ?: '{}',
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => 'application/json']
            );
        }

        if (!$this->articleSearchService->canAccessChannelForCurrentUser($channel)) {
            return new Response(
                json_encode(['msg' => 'Channel access denied']) ?: '{}',
                Response::HTTP_FORBIDDEN,
                ['Content-Type' => 'application/json']
            );
        }

        $ret = $this->articleSearchService->searchInChannel($channel, $q, explode(',', $contentTypeIds));

        if (\count($ret) > 0) {
            return new Response(json_encode($ret) ?: '[]', headers: ['Content-Type' => 'application/json']);
        }

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
