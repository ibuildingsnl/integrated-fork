<?php

namespace Integrated\Bundle\ApiBundle\Endpoint;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;

class SearchSelectionListEndpoint implements EndpointHandlerInterface
{
    public function __construct(private readonly ObjectRepository $searchSelectionRepository)
    {
    }

    public function handle(Request $request, ?string $id = null): array
    {
        $limit = max(1, min(100, (int) $request->query->get('limit', 25)));
        $documents = $this->searchSelectionRepository->findBy([], ['title' => 'asc'], $limit);

        $data = [];
        foreach ($documents as $document) {
            if (!is_object($document) || !method_exists($document, 'getId')) {
                continue;
            }

            $data[] = [
                'type' => 'search-selections',
                'id' => (string) $document->getId(),
                'attributes' => [
                    'title' => method_exists($document, 'getTitle') ? $document->getTitle() : null,
                    'public' => method_exists($document, 'isPublic') ? (bool) $document->isPublic() : null,
                    'locked' => method_exists($document, 'isLocked') ? (bool) $document->isLocked() : null,
                    'inMenu' => method_exists($document, 'isInMenu') ? (bool) $document->isInMenu() : null,
                ],
            ];
        }

        return [
            'data' => $data,
            'links' => [
                'self' => $request->getUri(),
            ],
            'meta' => [
                'count' => count($data),
                'limit' => $limit,
            ],
        ];
    }
}
