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

    public function searchContentByChannel(Request $request, ?string $channelId = null)
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

        $keyword = $request->query->get('term');
        $query = $this->getSolarium()->createSelect();
        $query->createFilterQuery('channels')
            ->addTag('channels')
            ->setQuery("title:{$keyword}");

        // Ensure the item has a non-empty 'url_vleesmagazine' field

        $result = $this->getSolarium()->select($query);
        $contentItems = $result->getDocuments();

        if ($contentItems) {
            return new Response(json_encode($contentItems), headers: ['Content-Type' => 'application/json']);
        } else {
            return new Response(
                json_encode([
                    'term' => $keyword,
                    'channelId' => $channelId,
                ]),
                status: Response::HTTP_NOT_FOUND,
                headers: ['Content-Type' => 'application/json']
            );
        }
    }
}
