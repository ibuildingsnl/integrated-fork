<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentHistoryBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentHistoryBundle\Document\ContentHistory;
use Integrated\Bundle\ContentHistoryBundle\History\Parser;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentHistoryController extends AbstractController
{
    private DocumentManager $manager;
    private Parser $parser;
    private PaginatorInterface $paginator;
    private ContentTypeManager $contentTypeManager;

    public function __construct(DocumentManager $manager, Parser $parser, PaginatorInterface $paginator, ContentTypeManager $contentTypeManager)
    {
        $this->manager = $manager;
        $this->parser = $parser;
        $this->paginator = $paginator;
        $this->contentTypeManager = $contentTypeManager;
    }

    public function index(Content $content, Request $request): Response
    {
        $contentType = $this->contentTypeManager->getType($content->getContentType());

        $builder = $this->manager->getRepository(ContentHistory::class)->createQueryBuilder();

        $builder->field('contentId')->equals($content->getId());
        $builder->sort('date', 'desc');

        $paginator = $this->paginator->paginate(
            $builder,
            $request->query->get('page', 1),
            $request->query->get('limit', 20)
        );

        return $this->render('@IntegratedContentHistory/content_history/index.html.twig', [
            'type' => $contentType,
            'content' => $content,
            'paginator' => $paginator,
        ]);
    }

    public function indexIframe(Content $content, Request $request): Response
    {
        $contentType = $this->contentTypeManager->getType($content->getContentType());

        $builder = $this->manager->getRepository(ContentHistory::class)->createQueryBuilder();

        $builder->field('contentId')->equals($content->getId());
        $builder->sort('date', 'desc');

        $paginator = $this->paginator->paginate(
            $builder,
            $request->query->get('page', 1),
            $request->query->get('limit', 20)
        );

        return $this->render('@IntegratedContentHistory/content_history/index.iframe.html.twig', [
            'type' => $contentType,
            'content' => $content,
            'paginator' => $paginator,
        ]);
    }

    public function show(Request $request, ContentHistory $contentHistory): Response
    {
        $content = $this->manager->find(Content::class, $contentHistory->getContentId());
        $contentType = $this->contentTypeManager->getType($content->getContentType());
        $historyRepo = $this->manager->getRepository(ContentHistory::class);
        $histories = $historyRepo->findBy(
            ['contentId' => $contentHistory->getContentId()],
            ['date' => 'asc']
        );

        $versions = [];
        $snapshots = [];
        $current = null;
        foreach ($histories as $index => $history) {
            $action = $history->getAction();
            if ($action === 'insert' || $action === 'delete') {
                $current = $history->getChangeSet();
            } elseif ($action === 'update') {
                if (!\is_array($current)) {
                    $current = [];
                }
                $current = $this->applyDiff($current, $history->getChangeSet());
            }

            $snapshots[] = $current ?? [];
            $versions[] = [
                'index' => $index,
                'id' => $history->getId(),
                'action' => $action,
                'date' => $history->getDate()->format('Y-m-d H:i:s'),
            ];
        }

        $toIndex = (int) $request->query->get('to', \max(\count($versions) - 1, 0));
        $fromIndex = (int) $request->query->get('from', \max($toIndex - 1, 0));
        $maxIndex = \max(\count($versions) - 1, 0);
        $toIndex = \max(0, \min($toIndex, $maxIndex));
        if ($maxIndex > 0) {
            $toIndex = \max(1, $toIndex);
        }
        $fromIndex = \max(0, \min($fromIndex, $maxIndex));
        if ($toIndex > 0 && $fromIndex >= $toIndex) {
            $fromIndex = $toIndex - 1;
        }

        $fromSnapshot = $snapshots[$fromIndex] ?? [];
        $toSnapshot = $snapshots[$toIndex] ?? [];
        $compareDiff = \Integrated\Bundle\ContentHistoryBundle\Diff\ArrayComparer::diff($fromSnapshot, $toSnapshot);
        $compareTable = $this->parser->getReadableChangesetFromArray($compareDiff);

        return $this->render('@IntegratedContentHistory/content_history/show.html.twig', [
            'type' => $contentType,
            'content' => $content,
            'contentHistory' => $contentHistory,
            'changeSet' => $this->parser->getReadableChangeset($contentHistory),
            'displayChangeSet' => $this->enhanceChangeSetForDisplay($this->parser->getReadableChangeset($contentHistory)),
            'versions' => $versions,
            'fromIndex' => $fromIndex,
            'toIndex' => $toIndex,
            'compareChangeSet' => $compareTable,
            'displayCompareChangeSet' => $this->enhanceChangeSetForDisplay($compareTable),
        ]);
    }

    public function showIframe(Request $request, ContentHistory $contentHistory): Response
    {
        $content = $this->manager->find(Content::class, $contentHistory->getContentId());
        $contentType = $this->contentTypeManager->getType($content->getContentType());

        return $this->render('@IntegratedContentHistory/content_history/show.iframe.html.twig', [
            'type' => $contentType,
            'content' => $content,
            'contentHistory' => $contentHistory,
            'changeSet' => $this->parser->getReadableChangeset($contentHistory),
            'displayChangeSet' => $this->enhanceChangeSetForDisplay($this->parser->getReadableChangeset($contentHistory)),
        ]);
    }

    private function enhanceChangeSetForDisplay(array $rows): array
    {
        return array_map(function (array $row): array {
            $row['oldDisplay'] = $this->formatHistoryValue($row['old'] ?? '');
            $row['newDisplay'] = $this->formatHistoryValue($row['new'] ?? '');

            return $row;
        }, $rows);
    }

    private function formatHistoryValue(mixed $value): array
    {
        if ($value === '' || $value === null) {
            return ['type' => 'empty', 'text' => ''];
        }

        if (is_bool($value)) {
            return ['type' => 'text', 'text' => $value ? 'true' : 'false'];
        }

        if (is_numeric($value)) {
            return ['type' => 'text', 'text' => (string) $value];
        }

        if (!is_string($value)) {
            return ['type' => 'text', 'text' => (string) $value];
        }

        $value = trim($value);
        if ($value === '') {
            return ['type' => 'empty', 'text' => ''];
        }

        $resolved = $this->resolveReferenceValue($value);
        if ($resolved !== null) {
            return $resolved;
        }

        if (preg_match('/<\s*(p|h[1-6]|ul|ol|li|blockquote|br)\b/i', $value)) {
            return [
                'type' => 'html',
                'html' => strip_tags($value, '<p><br><strong><em><b><i><u><a><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6>'),
            ];
        }

        $paragraphs = preg_split('/\R{2,}/', $value) ?: [];
        if (count($paragraphs) > 1) {
            return [
                'type' => 'paragraphs',
                'paragraphs' => array_values(array_filter(array_map('trim', $paragraphs), static fn (string $line): bool => $line !== '')),
            ];
        }

        return ['type' => 'text', 'text' => $value];
    }

    private function resolveReferenceValue(string $value): ?array
    {
        $contextLabel = null;

        if (preg_match('/^(?<label>[^:]+):\s*(?<class>[A-Za-z0-9_\\\\]+)\s+#(?<id>[A-Za-z0-9_]+)$/', $value, $prefixed)) {
            $contextLabel = trim($prefixed['label']);
            $value = sprintf('%s #%s', $prefixed['class'], $prefixed['id']);
        }

        if (preg_match('/^(?<collection>[a-z_]+)\s+#(?<id>[A-Za-z0-9_]+)$/i', $value, $matches)) {
            $collection = strtolower($matches['collection']);
            $id = $matches['id'];

            if ($collection === 'channel') {
                $channel = $this->manager->find(Channel::class, $id);
                if ($channel instanceof Channel) {
                    $text = sprintf('%s (#%s)', $channel->getName(), $channel->getId());
                    if ($contextLabel) {
                        $text = sprintf('%s: %s', $contextLabel, $text);
                    }

                    return [
                        'type' => 'reference',
                        'text' => $text,
                    ];
                }
            }
        }

        if (!preg_match('/^(?<class>[A-Za-z0-9_\\\\]+)\s+#(?<id>[A-Za-z0-9_]+)$/', $value, $matches)) {
            return null;
        }

        $class = $matches['class'];
        $id = $matches['id'];
        if (!class_exists($class)) {
            return null;
        }

        $document = $this->manager->find($class, $id);
        if (!$document) {
            return null;
        }

        if ($document instanceof Image) {
            $identifier = $document->getFile()?->getIdentifier();
            $title = trim((string) ($document->getTitle() ?? ''));
            $label = $title !== '' ? $title : sprintf('Image #%s', $id);
            if ($contextLabel) {
                $label = $contextLabel.($title !== '' ? ' - '.$title : '');
            }

            if ($identifier) {
                return [
                    'type' => 'image',
                    'src' => '/files/'.$identifier,
                    'label' => $label,
                ];
            }

            return ['type' => 'reference', 'text' => $label];
        }

        if ($document instanceof Channel) {
            $text = sprintf('%s (#%s)', $document->getName(), $document->getId());
            if ($contextLabel) {
                $text = sprintf('%s: %s', $contextLabel, $text);
            }

            return [
                'type' => 'reference',
                'text' => $text,
            ];
        }

        if (method_exists($document, 'getName')) {
            $name = trim((string) $document->getName());
            if ($name !== '') {
                return ['type' => 'reference', 'text' => $contextLabel ? sprintf('%s: %s', $contextLabel, $name) : $name];
            }
        }

        if (method_exists($document, 'getTitle')) {
            $title = trim((string) $document->getTitle());
            if ($title !== '') {
                return ['type' => 'reference', 'text' => $contextLabel ? sprintf('%s: %s', $contextLabel, $title) : $title];
            }
        }

        if (method_exists($document, 'getId')) {
            $text = sprintf('%s #%s', $this->shortClassName($class), $document->getId());

            return ['type' => 'reference', 'text' => $contextLabel ? sprintf('%s: %s', $contextLabel, $text) : $text];
        }

        $text = $this->shortClassName($class);

        return ['type' => 'reference', 'text' => $contextLabel ? sprintf('%s: %s', $contextLabel, $text) : $text];
    }

    private function shortClassName(string $class): string
    {
        $parts = explode('\\', $class);

        return (string) end($parts);
    }

    private function applyDiff(array $base, array $diff): array
    {
        foreach ($diff as $key => $value) {
            if (\is_array($value) && \array_key_exists(0, $value) && \array_key_exists(1, $value)
                && !\is_array($value[0]) && !\is_array($value[1])) {
                $base[$key] = $value[1];
                continue;
            }

            if (\is_array($value)) {
                $base[$key] = $this->applyDiff(\is_array($base[$key] ?? null) ? $base[$key] : [], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    public function history(Content $content, int $limit = 3): Response
    {
        return $this->render('@IntegratedContentHistory/content_history/history.html.twig', [
            'content' => $content,
            'documents' => $this->manager->getRepository(ContentHistory::class)->findBy(
                ['contentId' => $content->getId()],
                ['date' => 'desc'],
                $limit + 1
            ),
            'limit' => $limit,
        ]);
    }

    public function historyIframe(Content $content, int $limit = 3): Response
    {
        return $this->render('@IntegratedContentHistory/content_history/history.iframe.html.twig', [
            'content' => $content,
            'documents' => $this->manager->getRepository(ContentHistory::class)->findBy(
                ['contentId' => $content->getId()],
                ['date' => 'desc'],
                $limit + 1
            ),
            'limit' => $limit,
        ]);
    }
}
