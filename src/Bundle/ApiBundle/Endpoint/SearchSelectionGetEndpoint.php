<?php

namespace Integrated\Bundle\ApiBundle\Endpoint;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchSelectionGetEndpoint implements EndpointHandlerInterface
{
    public function __construct(private readonly ObjectRepository $searchSelectionRepository)
    {
    }

    public function handle(Request $request, ?string $id = null): array
    {
        if (null === $id || '' === $id) {
            throw new NotFoundHttpException('Missing search selection id.');
        }

        $document = $this->searchSelectionRepository->find($id);
        if (!is_object($document) || !method_exists($document, 'getId')) {
            throw new NotFoundHttpException(sprintf('Search selection "%s" was not found.', $id));
        }

        return [
            'data' => [
                'type' => 'search-selections',
                'id' => (string) $document->getId(),
                'attributes' => [
                    'title' => method_exists($document, 'getTitle') ? $document->getTitle() : null,
                    'public' => method_exists($document, 'isPublic') ? (bool) $document->isPublic() : null,
                    'locked' => method_exists($document, 'isLocked') ? (bool) $document->isLocked() : null,
                    'inMenu' => method_exists($document, 'isInMenu') ? (bool) $document->isInMenu() : null,
                ],
            ],
            'links' => [
                'self' => $request->getUri(),
            ],
        ];
    }
}
