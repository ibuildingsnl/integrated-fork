<?php

namespace Integrated\Bundle\ApiBundle\JsonApi;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DocumentResponseFactory
{
    /**
     * @param array<string, mixed> $document
     */
    public function create(array $document, int $status = Response::HTTP_OK): JsonResponse
    {
        if (!isset($document['jsonapi'])) {
            $document['jsonapi'] = ['version' => '1.0'];
        }

        $response = new JsonResponse($document, $status, [], false);
        $response->headers->set('Content-Type', 'application/vnd.api+json');

        return $response;
    }
}
