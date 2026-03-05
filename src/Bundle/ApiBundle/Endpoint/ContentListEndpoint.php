<?php

namespace Integrated\Bundle\ApiBundle\Endpoint;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;

class ContentListEndpoint implements EndpointHandlerInterface
{
    public function __construct(private readonly ObjectRepository $contentRepository)
    {
    }

    public function handle(Request $request, ?string $id = null): array
    {
        $limit = max(1, min(100, (int) $request->query->get('limit', 25)));
        $documents = $this->contentRepository->findBy([], ['updatedAt' => 'desc'], $limit);

        $data = [];
        foreach ($documents as $document) {
            if (!is_object($document) || !method_exists($document, 'getId')) {
                continue;
            }

            $data[] = [
                'type' => 'contents',
                'id' => (string) $document->getId(),
                'attributes' => [
                    'contentType' => method_exists($document, 'getContentType') ? $document->getContentType() : null,
                    'title' => method_exists($document, 'getTitle') ? $document->getTitle() : null,
                    'slug' => method_exists($document, 'getSlug') ? $document->getSlug() : null,
                    'published' => method_exists($document, 'isPublished') ? (bool) $document->isPublished() : null,
                    'updatedAt' => method_exists($document, 'getUpdatedAt') && $document->getUpdatedAt() instanceof \DateTimeInterface
                        ? $document->getUpdatedAt()->format(DATE_ATOM)
                        : null,
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
