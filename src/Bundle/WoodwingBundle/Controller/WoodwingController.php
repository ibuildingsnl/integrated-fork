<?php

namespace Integrated\Bundle\WoodwingBundle\Controller;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\WoodwingBundle\Document\WoodwingPost;
use Integrated\Common\Services\Flusher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WoodwingController extends AbstractController
{
    public function __construct(
        private readonly ObjectRepository $woodwingPosts,
        private readonly ObjectRepository $taxonomies,
        private readonly Flusher          $flusher,
        private readonly string           $apiSecret,
    ) {}

    public function index(Request $request): Response
    {
        $this->checkCredentials($request);

        /** @var WoodwingPost[] $unpublishedArticles */
        $unpublishedArticles = $this->woodwingPosts->findBy(['pickedUpAt' => null]);

        $editions = [];
        foreach ($unpublishedArticles as $article) {
            $editions[] = $article->edition->getId();
        }

        return new JsonResponse(["ids" => array_values(array_unique($editions))]);
    }

    public function show(string $issue, Request $request): Response
    {
        $this->checkCredentials($request);

        /** @var Taxonomy $edition */
        $edition = $this->taxonomies->find($issue);
        /** @var WoodwingPost[] $unpublishedArticles */
        $unpublishedArticles = $this->woodwingPosts->findBy(['pickedUpAt' => null, 'edition.id' => $issue]);

        return new JsonResponse([
            'publication_id' => $edition->getPrimaryChannel()?->getName() ?: 'Unknown',
            'issue_id' => $issue,
            'issue_name' => $edition->getTitle(),
            'articles' => array_map(fn(WoodwingPost $post) => $post->show(), $unpublishedArticles),
        ]);
    }

    public function markProcessed(string $article, Request $request): Response
    {
        $this->checkCredentials($request);

        /** @var WoodwingPost $post */
        $post = $this->woodwingPosts->find($article);

        $post->pickedUpAt = new \DateTime();

        $this->flusher->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    private function checkCredentials(Request $request): void
    {
        if ($request->get('secret') !== $this->apiSecret) {
            throw new AccessDeniedException();
        }
    }
}
