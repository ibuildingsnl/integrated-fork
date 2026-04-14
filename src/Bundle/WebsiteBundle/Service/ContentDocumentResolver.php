<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Service;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\ContentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ContentDocumentResolver
{
    public function __construct(
        private readonly ContentRepository $contentRepository,
    ) {
    }

    /**
     * @template T of Content
     *
     * @param class-string<T> $expectedClass
     *
     * @return T
     */
    public function resolve(Request $request, string $expectedClass): Content
    {
        $slug = trim((string) $request->attributes->get('slug'));
        if ($slug === '') {
            throw new NotFoundHttpException();
        }

        $document = $this->contentRepository->findOneBy(['slug' => $slug]);
        if (!$document instanceof $expectedClass) {
            throw new NotFoundHttpException();
        }

        return $document;
    }
}
