<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Validation\LayoutPayloadValidator;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\Channel\ChannelManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PageBuilderController extends AbstractController
{
    private const SECTION_PRESETS_OPTION = 'pagebuilder_section_presets';
    private const SECTION_PRESETS_LIMIT = 60;
    private const SECTION_PRESET_HTML_MAX_BYTES = 500000;
    private const SECTION_PRESETS_TOTAL_HTML_MAX_BYTES = 5000000;

    private DocumentManager $documentManager;
    private LayoutPayloadValidator $validator;
    private ChannelContextInterface $channelContext;
    private ChannelManagerInterface $channelManager;

    public function __construct(
        DocumentManager $documentManager,
        LayoutPayloadValidator $validator,
        ChannelContextInterface $channelContext,
        ChannelManagerInterface $channelManager,
    )
    {
        $this->documentManager = $documentManager;
        $this->validator = $validator;
        $this->channelContext = $channelContext;
        $this->channelManager = $channelManager;
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

    public function listSectionPresets(): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $channel = $this->channelContext->getChannel();
        if (!$channel instanceof ChannelInterface) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No active channel found',
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
            'presets' => $this->normalizeSectionPresets((array) $channel->getOption(self::SECTION_PRESETS_OPTION)),
        ]);
    }

    public function saveSectionPreset(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $channel = $this->channelContext->getChannel();
        if (!$channel instanceof ChannelInterface) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No active channel found',
            ], 404);
        }

        $data = (array) json_decode((string) $request->getContent(), true);
        $name = trim((string) ($data['name'] ?? ''));
        $html = trim((string) ($data['html'] ?? ''));
        $presetId = trim((string) ($data['id'] ?? ''));

        if ($name === '' || $html === '') {
            return new JsonResponse([
                'success' => false,
                'error' => 'Preset name and html are required',
            ], 400);
        }

        if (\strlen($name) > 120) {
            $name = \substr($name, 0, 120);
        }

        if (\strlen($html) > self::SECTION_PRESET_HTML_MAX_BYTES) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Preset html exceeds maximum size',
            ], 413);
        }

        $presets = $this->normalizeSectionPresets((array) $channel->getOption(self::SECTION_PRESETS_OPTION));
        $now = (new \DateTimeImmutable())->format(\DATE_ATOM);
        $matchedIndex = -1;

        if ($presetId !== '') {
            foreach ($presets as $index => $preset) {
                if (($preset['id'] ?? '') === $presetId) {
                    $matchedIndex = $index;
                    break;
                }
            }
        }

        if ($presetId === '') {
            $presetId = \bin2hex(\random_bytes(8));
        }

        $record = [
            'id' => $presetId,
            'name' => $name,
            'html' => $html,
            'updatedAt' => $now,
        ];

        if ($matchedIndex >= 0) {
            $presets[$matchedIndex] = $record;
        } else {
            array_unshift($presets, $record);
        }

        $presets = array_values(array_slice($presets, 0, self::SECTION_PRESETS_LIMIT));

        // Keep channel options bounded; the channel document has a hard BSON limit.
        $boundedPresets = [];
        $totalHtmlBytes = 0;
        foreach ($presets as $preset) {
            $presetHtml = (string) ($preset['html'] ?? '');
            $presetHtmlBytes = \strlen($presetHtml);

            if ($totalHtmlBytes + $presetHtmlBytes > self::SECTION_PRESETS_TOTAL_HTML_MAX_BYTES) {
                continue;
            }

            $boundedPresets[] = $preset;
            $totalHtmlBytes += $presetHtmlBytes;
        }
        $presets = $boundedPresets;

        $channel->setOption(self::SECTION_PRESETS_OPTION, $presets);

        try {
            $this->channelManager->persist($channel, true);
        } catch (Throwable $exception) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Unable to persist section preset',
                'details' => $exception->getMessage(),
            ], 500);
        }

        return new JsonResponse([
            'success' => true,
            'preset' => $record,
            'count' => \count($presets),
        ]);
    }

    public function deleteSectionPreset(string $id): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $channel = $this->channelContext->getChannel();
        if (!$channel instanceof ChannelInterface) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No active channel found',
            ], 404);
        }

        $presetId = trim($id);
        if ($presetId === '') {
            return new JsonResponse([
                'success' => false,
                'error' => 'Preset id is required',
            ], 400);
        }

        $presets = $this->normalizeSectionPresets((array) $channel->getOption(self::SECTION_PRESETS_OPTION));
        $presets = array_values(array_filter($presets, static function(array $preset) use ($presetId): bool {
            return ($preset['id'] ?? '') !== $presetId;
        }));

        $channel->setOption(self::SECTION_PRESETS_OPTION, $presets);

        try {
            $this->channelManager->persist($channel, true);
        } catch (Throwable $exception) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Unable to delete section preset',
                'details' => $exception->getMessage(),
            ], 500);
        }

        return new JsonResponse([
            'success' => true,
            'count' => \count($presets),
        ]);
    }

    /**
     * @param array<int|string, mixed> $presets
     *
     * @return array<int, array{id:string,name:string,html:string,updatedAt:string}>
     */
    private function normalizeSectionPresets(array $presets): array
    {
        $normalized = [];
        foreach ($presets as $preset) {
            if (!\is_array($preset)) {
                continue;
            }

            $id = trim((string) ($preset['id'] ?? ''));
            $name = trim((string) ($preset['name'] ?? ''));
            $html = trim((string) ($preset['html'] ?? ''));
            $updatedAt = trim((string) ($preset['updatedAt'] ?? ''));

            if ($id === '' || $name === '' || $html === '') {
                continue;
            }

            $normalized[] = [
                'id' => $id,
                'name' => $name,
                'html' => $html,
                'updatedAt' => $updatedAt,
            ];
        }

        return $normalized;
    }
}
