<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Validation\LayoutPayloadValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageBuilderController extends AbstractController
{
    private DocumentManager $documentManager;
    private LayoutPayloadValidator $validator;

    public function __construct(DocumentManager $documentManager, LayoutPayloadValidator $validator)
    {
        $this->documentManager = $documentManager;
        $this->validator = $validator;
    }

    public function save(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $data = (array) json_decode((string) $request->getContent(), true);

        $pageId = isset($data['page']) ? (string) $data['page'] : '';
        if ($pageId === '') {
            return new JsonResponse(['success' => false, 'error' => 'No page specified'], 400);
        }

        $payload = isset($data['payload']) && \is_array($data['payload']) ? $data['payload'] : [];
        $meta = isset($data['meta']) && \is_array($data['meta']) ? $data['meta'] : [];
        $theme = isset($data['theme']) ? (string) $data['theme'] : 'default';
        $expectedRevision = array_key_exists('expectedRevision', $data) ? (int) $data['expectedRevision'] : null;
        $force = !empty($data['force']);

        $page = $this->documentManager->getRepository(AbstractPage::class)->find($pageId);
        if (!$page instanceof AbstractPage) {
            return new JsonResponse(['success' => false, 'error' => 'Page not found'], 404);
        }

        $currentLayoutMeta = $page->getLayoutMeta();
        $currentRevision = isset($currentLayoutMeta['revision']) ? (int) $currentLayoutMeta['revision'] : 0;
        if ($expectedRevision !== null && $expectedRevision !== $currentRevision && $force !== true) {
            return new JsonResponse([
                'success' => false,
                'conflict' => true,
                'error' => 'Page has changed on the server',
                'currentRevision' => $currentRevision,
            ], 409);
        }

        $errors = $this->validator->validate($payload, $theme);
        if (\count($errors) > 0) {
            return new JsonResponse([
                'success' => false,
                'errors' => array_map(static fn ($error) => $error->toArray(), $errors),
            ], 422);
        }

        $legacy = $page->getLegacy();
        if (!isset($legacy['grids'])) {
            $legacy['grids'] = array_map(static function ($grid) {
                if (\is_object($grid) && method_exists($grid, 'toArray')) {
                    return $grid->toArray();
                }

                return null;
            }, $page->getGrids());
        }

        $page->setLayoutVersion(2);
        $page->setLayoutPayload($payload);
        $nextRevision = $currentRevision + 1;
        $page->setLayoutMeta(array_merge($currentLayoutMeta, $meta, ['revision' => $nextRevision]));
        $page->setLegacy($legacy);

        $this->documentManager->flush();

        return new JsonResponse([
            'success' => true,
            'revision' => $nextRevision,
        ]);
    }
}
