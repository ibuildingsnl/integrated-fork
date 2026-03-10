<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\PageBundle\Document\Page\PageEditDraft;
use Integrated\Bundle\PageBundle\Document\Page\PageEditDraftRepository;
use Integrated\Bundle\PageBundle\Grid\GridFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;

class PageDraftController extends AbstractController
{
    private const PREVIEW_EXPIRES_PARAM = 'preview_expires';
    private const PREVIEW_DRAFT_PARAM = 'page_draft_preview';
    private const PREVIEW_TTL_SECONDS = 86400;

    public function __construct(
        private DocumentManager $documentManager,
        private GridFactory $gridFactory,
        private UriSigner $uriSigner,
    ) {
    }

    public function getDraft(Request $request, string $id): JsonResponse
    {
        $this->assertCanEditWebsite();

        if (!$this->findPageByIdentifier($id)) {
            return new JsonResponse(['message' => 'Page not found.'], Response::HTTP_NOT_FOUND);
        }

        $userId = $this->getCurrentUserIdentifier();
        if ($userId === '') {
            return new JsonResponse(['exists' => false]);
        }

        $draft = $this->findDraftByPageAndUser($id, $userId);
        if (!$draft instanceof PageEditDraft) {
            return new JsonResponse(['exists' => false]);
        }

        return new JsonResponse([
            'exists' => true,
            'gridPayload' => $draft->getGridPayload(),
            'menuPayload' => $draft->getMenuPayload(),
            'basePageUpdatedAt' => $draft->getBasePageUpdatedAt(),
            'updatedAt' => $draft->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'versions' => $this->normalizeDraftVersions($draft),
        ]);
    }

    public function saveDraft(Request $request, string $id): JsonResponse
    {
        $this->assertCanEditWebsite();

        if (!$this->findPageByIdentifier($id)) {
            return new JsonResponse(['message' => 'Page not found.'], Response::HTTP_NOT_FOUND);
        }

        $userId = $this->getCurrentUserIdentifier();
        if ($userId === '') {
            return new JsonResponse(['message' => 'No authenticated user found.'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $this->extractDraftRequestData($request);
        $gridPayload = $payload['gridPayload'];
        $menuPayload = $payload['menuPayload'];
        $basePageUpdatedAt = $payload['basePageUpdatedAt'];

        $draft = $this->findDraftByPageAndUser($id, $userId);
        if (!$draft instanceof PageEditDraft) {
            $draft = new PageEditDraft($id, $userId);
            $this->documentManager->persist($draft);
        }

        $gridChanged = $draft->getGridPayload() !== $gridPayload;
        $menuChanged = $draft->getMenuPayload() !== $menuPayload;
        $payloadChanged = $gridChanged || $menuChanged;
        if ($payloadChanged) {
            $draft->pushVersion($gridPayload, $menuPayload);
        }

        $draft->setGridPayload($gridPayload);
        $draft->setMenuPayload($menuPayload);
        $draft->setBasePageUpdatedAt($basePageUpdatedAt);

        $this->documentManager->flush();

        return new JsonResponse([
            'saved' => true,
            'changed' => $payloadChanged,
            'updatedAt' => $draft->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'versions' => $this->normalizeDraftVersions($draft),
        ]);
    }

    public function deleteDraft(Request $request, string $id): JsonResponse
    {
        $this->assertCanEditWebsite();

        if (!$this->findPageByIdentifier($id)) {
            return new JsonResponse(['message' => 'Page not found.'], Response::HTTP_NOT_FOUND);
        }

        $userId = $this->getCurrentUserIdentifier();
        if ($userId === '') {
            return new JsonResponse(['removed' => false]);
        }

        $draft = $this->findDraftByPageAndUser($id, $userId);
        if (!$draft instanceof PageEditDraft) {
            return new JsonResponse(['removed' => false]);
        }

        $this->documentManager->remove($draft);
        $this->documentManager->flush();

        return new JsonResponse(['removed' => true]);
    }

    public function publishDraft(Request $request, string $id): JsonResponse
    {
        $this->assertCanEditWebsite();

        $page = $this->findPageByIdentifier($id);
        if (!$page instanceof AbstractPage) {
            return new JsonResponse(['message' => 'Page not found.'], Response::HTTP_NOT_FOUND);
        }

        $userId = $this->getCurrentUserIdentifier();
        if ($userId === '') {
            return new JsonResponse(['message' => 'No authenticated user found.'], Response::HTTP_UNAUTHORIZED);
        }

        $draft = $this->findDraftByPageAndUser($id, $userId);
        if (!$draft instanceof PageEditDraft) {
            return new JsonResponse(['message' => 'Draft not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($this->isPageUpdatedAfterBaseline($page, $draft->getBasePageUpdatedAt())) {
            return new JsonResponse([
                'published' => false,
                'conflict' => true,
                'message' => 'Page changed since this draft session started. Reload editor before publishing.',
            ], Response::HTTP_CONFLICT);
        }

        $grids = [];
        foreach ($draft->getGridPayload() as $gridPayload) {
            if (!\is_array($gridPayload)) {
                continue;
            }

            $grid = $this->gridFactory->fromArray($gridPayload);
            if ($grid instanceof Grid) {
                $grids[] = $grid;
            }
        }

        $page->setGrids($grids);
        $page->setUpdatedAt(new \DateTime());

        $this->documentManager->remove($draft);
        $this->documentManager->flush();

        return new JsonResponse([
            'published' => true,
            'updatedAt' => $page->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ]);
    }

    public function createPreviewLink(Request $request, string $id): JsonResponse
    {
        $this->assertCanEditWebsite();

        $page = $this->findPageByIdentifier($id);
        if (!$page instanceof AbstractPage) {
            return new JsonResponse(['message' => 'Page not found.'], Response::HTTP_NOT_FOUND);
        }

        $userId = $this->getCurrentUserIdentifier();
        if ($userId === '') {
            return new JsonResponse(['message' => 'No authenticated user found.'], Response::HTTP_UNAUTHORIZED);
        }

        $draft = $this->findDraftByPageAndUser($id, $userId);
        if (!$draft instanceof PageEditDraft) {
            return new JsonResponse(['message' => 'Draft not found.'], Response::HTTP_NOT_FOUND);
        }

        $expires = time() + self::PREVIEW_TTL_SECONDS;
        $url = $this->buildAbsolutePageUrl($page, $request, [
            self::PREVIEW_EXPIRES_PARAM => $expires,
            self::PREVIEW_DRAFT_PARAM => $draft->getId(),
        ]);

        return new JsonResponse([
            'created' => true,
            'url' => $this->uriSigner->sign($url),
            'expiresAt' => (new \DateTimeImmutable('@'.$expires))->format(\DateTimeInterface::ATOM),
        ]);
    }

    private function assertCanEditWebsite(): void
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
    }

    private function findPageByIdentifier(string $id): ?AbstractPage
    {
        $repository = $this->documentManager->getRepository(AbstractPage::class);
        $page = $repository->find($id);

        return $page instanceof AbstractPage ? $page : null;
    }

    private function isPageUpdatedAfterBaseline(AbstractPage $page, ?string $baseline): bool
    {
        if ($baseline === null || trim($baseline) === '') {
            return false;
        }

        try {
            $baselineDate = new \DateTimeImmutable($baseline);
        } catch (\Exception) {
            return false;
        }

        $updatedAt = $page->getUpdatedAt();
        if (!$updatedAt instanceof \DateTimeInterface) {
            return false;
        }

        return $updatedAt->getTimestamp() > $baselineDate->getTimestamp();
    }

    private function findDraftByPageAndUser(string $pageId, string $userId): ?PageEditDraft
    {
        /** @var PageEditDraftRepository $repository */
        $repository = $this->documentManager->getRepository(PageEditDraft::class);

        if (method_exists($repository, 'findOneByPageAndUser')) {
            return $repository->findOneByPageAndUser($pageId, $userId);
        }

        /** @var PageEditDraft|null $draft */
        $draft = $repository->findOneBy([
            'pageId' => $pageId,
            'userId' => $userId,
        ]);

        return $draft;
    }

    private function getCurrentUserIdentifier(): string
    {
        $user = $this->getUser();
        if (!\is_object($user)) {
            return '';
        }

        if (method_exists($user, 'getUserIdentifier')) {
            return (string) $user->getUserIdentifier();
        }

        if (method_exists($user, 'getId')) {
            return (string) $user->getId();
        }

        return '';
    }

    /**
     * @return array{
     *     gridPayload: array<mixed>,
     *     menuPayload: array<mixed>,
     *     basePageUpdatedAt: string|null
     * }
     */
    private function extractDraftRequestData(Request $request): array
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!\is_array($payload)) {
            $payload = [];
        }

        $gridPayload = \is_array($payload['gridPayload'] ?? null) ? $payload['gridPayload'] : [];
        $menuPayload = \is_array($payload['menuPayload'] ?? null) ? $payload['menuPayload'] : [];
        $basePageUpdatedAt = isset($payload['basePageUpdatedAt']) ? (string) $payload['basePageUpdatedAt'] : null;

        return [
            'gridPayload' => $gridPayload,
            'menuPayload' => $menuPayload,
            'basePageUpdatedAt' => $basePageUpdatedAt !== '' ? $basePageUpdatedAt : null,
        ];
    }

    /**
     * @return array<int, array{id: string, savedAt: string}>
     */
    private function normalizeDraftVersions(PageEditDraft $draft): array
    {
        $versions = [];
        foreach ($draft->getVersions() as $version) {
            $versions[] = [
                'id' => $version->getId(),
                'savedAt' => $version->getSavedAt()->format(\DateTimeInterface::ATOM),
            ];
        }

        return $versions;
    }

    /**
     * @param array<string, scalar> $query
     */
    private function buildAbsolutePageUrl(AbstractPage $page, Request $request, array $query = []): string
    {
        $host = trim((string) ($page->getDomain() ?: $request->getHost()));
        if ($host === '') {
            $host = (string) $request->getHost();
        }

        $scheme = $request->getScheme() ?: 'https';
        $path = (string) $page->getPath();
        $queryString = http_build_query($query, '', '&', \PHP_QUERY_RFC3986);

        return $scheme.'://'.$host.$path.($queryString !== '' ? '?'.$queryString : '');
    }
}
