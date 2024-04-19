<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleSearchController extends AbstractController
{
    public function index()
    {
        return $this->render('@IntegratedContent/article_search/article_search.html.twig');
    }

    public function search(Request $request)
    {
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
