<?php

namespace Integrated\Bundle\ApiBundle\Endpoint;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentGetEndpoint implements EndpointHandlerInterface
{
    public function __construct(private readonly ObjectRepository $contentRepository)
    {
    }

    public function handle(Request $request, ?string $id = null): array
    {
        if (null === $id || '' === $id) {
            throw new NotFoundHttpException('Missing content id.');
        }

        $document = $this->contentRepository->find($id);
        if (!is_object($document) || !method_exists($document, 'getId')) {
            throw new NotFoundHttpException(sprintf('Content "%s" was not found.', $id));
        }

        return [
            'data' => [
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
            ],
            'links' => [
                'self' => $request->getUri(),
            ],
        ];
    }
}
