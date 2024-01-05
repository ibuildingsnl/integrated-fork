<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Controller;

use Integrated\Bundle\ContentBundle\Solr\Query\SuggestionQuery;
use Solarium\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class SearchController extends AbstractController
{
    private Client $client;
    private Serializer $serializer;

    public function __construct(Client $client, Serializer $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    public function suggestion(string $query, Request $request): Response
    {
        $response = ['query' => ''];

        if ($query = trim($query)) {
            $response = $this->client->select(new SuggestionQuery($query));
        }

        return new Response($this->serializer->serialize($response, $request->getRequestFormat('json')));
    }
}
