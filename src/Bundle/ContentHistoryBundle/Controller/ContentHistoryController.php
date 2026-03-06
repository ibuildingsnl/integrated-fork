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
use Doctrine\ODM\MongoDB\Query\Builder as MongoQueryBuilder;
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
    private array $formattedValueCache = [];
    private array $referenceDocumentCache = [];

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
        $filters = $this->extractHistoryFilters($request);
        $builder = $this->buildHistoryQuery($content->getId(), $filters);

        $paginator = $this->paginator->paginate(
            $builder,
            $request->query->get('page', 1),
            $request->query->get('limit', 20)
        );

        return $this->render('@IntegratedContentHistory/content_history/index.html.twig', [
            'type' => $contentType,
            'content' => $content,
            'paginator' => $paginator,
            'filters' => $filters,
        ]);
    }

    public function indexIframe(Content $content, Request $request): Response
    {
        $contentType = $this->contentTypeManager->getType($content->getContentType());
        $filters = $this->extractHistoryFilters($request);
        $builder = $this->buildHistoryQuery($content->getId(), $filters);

        $paginator = $this->paginator->paginate(
            $builder,
            $request->query->get('page', 1),
            $request->query->get('limit', 20)
        );

        return $this->render('@IntegratedContentHistory/content_history/index.iframe.html.twig', [
            'type' => $contentType,
            'content' => $content,
            'paginator' => $paginator,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, ContentHistory $contentHistory): Response
    {
        $showTechnical = $request->query->getBoolean('technical', false);

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

        $fromSnapshot = $this->normalizeSnapshotForCompare($snapshots[$fromIndex] ?? []);
        $toSnapshot = $this->normalizeSnapshotForCompare($snapshots[$toIndex] ?? []);
        $compareDiff = \Integrated\Bundle\ContentHistoryBundle\Diff\ArrayComparer::diff($fromSnapshot, $toSnapshot);
        $compareTable = $this->parser->getReadableChangesetFromArray($compareDiff);
        [$filteredCompareTable, $hiddenCompareRows] = $this->filterReadableRows($compareTable, $showTechnical);
        $revisionChangeSet = $this->parser->getReadableChangeset($contentHistory);
        [$filteredRevisionChangeSet, $hiddenRevisionRows] = $this->filterReadableRows($revisionChangeSet, $showTechnical);

        return $this->render('@IntegratedContentHistory/content_history/show.html.twig', [
            'type' => $contentType,
            'content' => $content,
            'contentHistory' => $contentHistory,
            'showTechnical' => $showTechnical,
            'hiddenRevisionRows' => $hiddenRevisionRows,
            'hiddenCompareRows' => $hiddenCompareRows,
            'changeSet' => $filteredRevisionChangeSet,
            'displayChangeSet' => $this->enhanceChangeSetForDisplay($filteredRevisionChangeSet),
            'versions' => $versions,
            'fromIndex' => $fromIndex,
            'toIndex' => $toIndex,
            'compareChangeSet' => $filteredCompareTable,
            'displayCompareChangeSet' => $this->enhanceChangeSetForDisplay($filteredCompareTable),
        ]);
    }

    public function showIframe(Request $request, ContentHistory $contentHistory): Response
    {
        $showTechnical = $request->query->getBoolean('technical', false);

        $content = $this->manager->find(Content::class, $contentHistory->getContentId());
        $contentType = $this->contentTypeManager->getType($content->getContentType());
        $revisionChangeSet = $this->parser->getReadableChangeset($contentHistory);
        [$filteredRevisionChangeSet, $hiddenRevisionRows] = $this->filterReadableRows($revisionChangeSet, $showTechnical);

        return $this->render('@IntegratedContentHistory/content_history/show.iframe.html.twig', [
            'type' => $contentType,
            'content' => $content,
            'contentHistory' => $contentHistory,
            'showTechnical' => $showTechnical,
            'hiddenRevisionRows' => $hiddenRevisionRows,
            'changeSet' => $filteredRevisionChangeSet,
            'displayChangeSet' => $this->enhanceChangeSetForDisplay($filteredRevisionChangeSet),
        ]);
    }

    private function enhanceChangeSetForDisplay(array $rows): array
    {
        return array_map(function (array $row): array {
            $row['displayName'] = $this->humanizeHistoryFieldName((string) ($row['name'] ?? ''));
            $row['oldDisplay'] = $this->formatHistoryValue($row['old'] ?? '');
            $row['newDisplay'] = $this->formatHistoryValue($row['new'] ?? '');
            $row = $this->applyInlineDiffForTextRow($row);

            return $row;
        }, $rows);
    }

    private function filterReadableRows(array $rows, bool $showTechnical): array
    {
        if ($showTechnical) {
            return [$rows, 0];
        }

        $filtered = [];
        $hidden = 0;

        foreach ($rows as $row) {
            if ($this->isNoOpRow($row) || $this->isTechnicalRowName((string) ($row['name'] ?? ''))) {
                ++$hidden;
                continue;
            }

            $filtered[] = $row;
        }

        return [$filtered, $hidden];
    }

    private function isNoOpRow(array $row): bool
    {
        $old = trim((string) ($row['old'] ?? ''));
        $new = trim((string) ($row['new'] ?? ''));

        return $old !== '' && $old === $new;
    }

    private function isTechnicalRowName(string $name): bool
    {
        $normalized = strtolower(trim($name));
        if ($normalized === '') {
            return false;
        }

        if (preg_match('/(^| > )(relationid|relationtype)( |$)/', $normalized)) {
            return true;
        }

        if (str_contains($normalized, ' > references > ')) {
            return true;
        }

        if (preg_match('/(^| > )(_?\\$id|_?\\$ref|class)( |$)/', $normalized)) {
            return true;
        }

        return false;
    }

    private function humanizeHistoryFieldName(string $path): string
    {
        if ($path === '') {
            return '';
        }

        $map = [
            'title' => 'Title',
            'slug' => 'Slug',
            'intro' => 'Intro',
            'content' => 'Content',
            'authors' => 'Authors',
            'channels' => 'Channels',
            'publishTime' => 'Publication',
            'startDate' => 'Publication date',
            'endDate' => 'Depublication date',
            'metaTitle' => 'SEO title',
            'metaDescription' => 'SEO description',
            'relations' => 'Relations',
            'relationId' => 'Relation',
            'relationType' => 'Relation type',
            'references' => 'References',
            'workflow' => 'Workflow',
            'state' => 'Status',
            'createdAt' => 'Created at',
            'updatedAt' => 'Updated at',
        ];

        $segments = array_map('trim', explode(' > ', $path));
        $displaySegments = [];

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            if (ctype_digit($segment)) {
                $displaySegments[] = '#'.((int) $segment + 1);
                continue;
            }

            $cleanSegment = ltrim($segment, '_');
            $label = $map[$cleanSegment] ?? null;
            if ($label === null) {
                $cleanSegment = str_replace(['_', '-'], ' ', $cleanSegment);
                $label = ucfirst($cleanSegment);
            }

            $displaySegments[] = $label;
        }

        return implode(' > ', $displaySegments);
    }

    private function formatHistoryValue(mixed $value): array
    {
        $cacheKey = md5(is_scalar($value) || $value === null ? (string) $value : serialize($value));
        if (isset($this->formattedValueCache[$cacheKey])) {
            return $this->formattedValueCache[$cacheKey];
        }

        if ($value === '' || $value === null) {
            return $this->formattedValueCache[$cacheKey] = ['type' => 'empty', 'text' => ''];
        }

        if (is_bool($value)) {
            return $this->formattedValueCache[$cacheKey] = ['type' => 'text', 'text' => $value ? 'true' : 'false'];
        }

        if (is_numeric($value)) {
            return $this->formattedValueCache[$cacheKey] = ['type' => 'text', 'text' => (string) $value];
        }

        if (!is_string($value)) {
            return $this->formattedValueCache[$cacheKey] = ['type' => 'text', 'text' => (string) $value];
        }

        $value = trim($value);
        if ($value === '') {
            return $this->formattedValueCache[$cacheKey] = ['type' => 'empty', 'text' => ''];
        }

        $resolvedComposite = $this->resolveCompositeReferenceValue($value);
        if ($resolvedComposite !== null) {
            return $this->formattedValueCache[$cacheKey] = $resolvedComposite;
        }

        $resolved = $this->resolveReferenceValue($value);
        if ($resolved !== null) {
            return $this->formattedValueCache[$cacheKey] = $resolved;
        }

        if (preg_match('/<\s*(p|h[1-6]|ul|ol|li|blockquote|br)\b/i', $value)) {
            return $this->formattedValueCache[$cacheKey] = [
                'type' => 'html',
                'html' => strip_tags($value, '<p><br><strong><em><b><i><u><a><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6>'),
            ];
        }

        $paragraphs = preg_split('/\R{2,}/', $value) ?: [];
        if (count($paragraphs) > 1) {
            return $this->formattedValueCache[$cacheKey] = [
                'type' => 'paragraphs',
                'paragraphs' => array_values(array_filter(array_map('trim', $paragraphs), static fn (string $line): bool => $line !== '')),
            ];
        }

        return $this->formattedValueCache[$cacheKey] = ['type' => 'text', 'text' => $value];
    }

    private function resolveCompositeReferenceValue(string $value): ?array
    {
        if (!str_contains($value, ',')) {
            return null;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $part): bool => $part !== ''));
        if (\count($parts) < 2) {
            return null;
        }

        $items = [];
        $matchedReferences = 0;

        foreach ($parts as $part) {
            $resolved = $this->resolveReferenceValue($part);
            if ($resolved !== null) {
                $items[] = $resolved;
                ++$matchedReferences;
                continue;
            }

            $items[] = ['type' => 'text', 'text' => $part];
        }

        if ($matchedReferences < 2) {
            return null;
        }

        return ['type' => 'stack', 'items' => $items];
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
                        'href' => $this->generateUrl('integrated_content_channel_edit', ['id' => $channel->getId()]),
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

        $contextLabel = $this->sanitizeContextLabel($contextLabel, $class, null);

        $cacheId = $class.'#'.$id;
        if (array_key_exists($cacheId, $this->referenceDocumentCache)) {
            $document = $this->referenceDocumentCache[$cacheId];
        } else {
            $document = $this->manager->find($class, $id);
            $this->referenceDocumentCache[$cacheId] = $document ?: null;
        }

        $contextLabel = $this->sanitizeContextLabel($contextLabel, $class, $document);

        if (!$document) {
            $text = sprintf('%s #%s', $this->shortClassName($class), $id);

            return ['type' => 'reference', 'text' => $contextLabel ? sprintf('%s: %s', $contextLabel, $text) : $text];
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
                    'href' => $this->generateUrl('integrated_content_content_edit', ['id' => $id]),
                ];
            }

            return [
                'type' => 'reference',
                'text' => $label,
                'href' => $this->generateUrl('integrated_content_content_edit', ['id' => $id]),
            ];
        }

        if ($document instanceof Channel) {
            $text = sprintf('%s (#%s)', $document->getName(), $document->getId());
            if ($contextLabel) {
                $text = sprintf('%s: %s', $contextLabel, $text);
            }

            return [
                'type' => 'reference',
                'text' => $text,
                'href' => $this->generateUrl('integrated_content_channel_edit', ['id' => $document->getId()]),
            ];
        }

        if (method_exists($document, 'getName')) {
            $name = trim((string) $document->getName());
            if ($name !== '') {
                return [
                    'type' => 'reference',
                    'text' => $contextLabel ? sprintf('%s: %s', $contextLabel, $name) : $name,
                    'href' => method_exists($document, 'getId') ? $this->generateUrl('integrated_content_content_edit', ['id' => $document->getId()]) : null,
                ];
            }
        }

        if (method_exists($document, 'getTitle')) {
            $title = trim((string) $document->getTitle());
            if ($title !== '') {
                return [
                    'type' => 'reference',
                    'text' => $contextLabel ? sprintf('%s: %s', $contextLabel, $title) : $title,
                    'href' => method_exists($document, 'getId') ? $this->generateUrl('integrated_content_content_edit', ['id' => $document->getId()]) : null,
                ];
            }
        }

        if (method_exists($document, 'getId')) {
            $text = sprintf('%s #%s', $this->shortClassName($class), $document->getId());

            return ['type' => 'reference', 'text' => $contextLabel ? sprintf('%s: %s', $contextLabel, $text) : $text];
        }

        $text = $this->shortClassName($class);

        return ['type' => 'reference', 'text' => $contextLabel ? sprintf('%s: %s', $contextLabel, $text) : $text];
    }

    private function sanitizeContextLabel(?string $contextLabel, string $class, ?object $document): ?string
    {
        if ($contextLabel === null || trim($contextLabel) === '') {
            return null;
        }

        $normalizedLabel = strtolower(trim($contextLabel));
        $isImageReference = $document instanceof Image || str_ends_with($class, '\\Image');
        $isPersonReference = str_ends_with($class, '\\Person');

        if (str_contains($normalizedLabel, 'image') && !$isImageReference) {
            return null;
        }

        if (str_contains($normalizedLabel, 'author') && !$isPersonReference) {
            return null;
        }

        return $contextLabel;
    }

    private function shortClassName(string $class): string
    {
        $parts = explode('\\', $class);

        return (string) end($parts);
    }

    private function applyInlineDiffForTextRow(array $row): array
    {
        $name = strtolower((string) ($row['name'] ?? ''));
        $old = (string) ($row['old'] ?? '');
        $new = (string) ($row['new'] ?? '');

        if (!$this->shouldApplyInlineDiff($name, $old, $new)) {
            return $row;
        }

        $oldTokens = preg_split('/\s+/', trim($old)) ?: [];
        $newTokens = preg_split('/\s+/', trim($new)) ?: [];
        $oldLookup = array_flip($oldTokens);
        $newLookup = array_flip($newTokens);

        $oldHtml = [];
        foreach ($oldTokens as $token) {
            $safe = htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if (!array_key_exists($token, $newLookup)) {
                $oldHtml[] = '<span class="history-diff-del">'.$safe.'</span>';
            } else {
                $oldHtml[] = $safe;
            }
        }

        $newHtml = [];
        foreach ($newTokens as $token) {
            $safe = htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if (!array_key_exists($token, $oldLookup)) {
                $newHtml[] = '<span class="history-diff-add">'.$safe.'</span>';
            } else {
                $newHtml[] = $safe;
            }
        }

        $row['oldDisplay'] = ['type' => 'html', 'html' => implode(' ', $oldHtml)];
        $row['newDisplay'] = ['type' => 'html', 'html' => implode(' ', $newHtml)];

        return $row;
    }

    private function shouldApplyInlineDiff(string $fieldName, string $old, string $new): bool
    {
        if ($old === '' || $new === '' || $old === $new) {
            return false;
        }

        $textualFields = ['content', 'intro', 'summary', 'meta', 'description', 'title'];
        $isTextField = false;
        foreach ($textualFields as $field) {
            if (str_contains($fieldName, $field)) {
                $isTextField = true;
                break;
            }
        }

        if (!$isTextField) {
            return false;
        }

        return (strlen($old) > 40 || strlen($new) > 40);
    }

    private function extractHistoryFilters(Request $request): array
    {
        return [
            'q' => trim((string) $request->query->get('q', '')),
            'action' => trim((string) $request->query->get('action', '')),
            'user' => trim((string) $request->query->get('user', '')),
            'from' => trim((string) $request->query->get('from', '')),
            'to' => trim((string) $request->query->get('to', '')),
        ];
    }

    private function normalizeSnapshotForCompare(array $snapshot): array
    {
        $normalized = $this->normalizeCompareValue($snapshot);

        return is_array($normalized) ? $normalized : [];
    }

    private function normalizeCompareValue(mixed $value, ?string $parentKey = null): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if ($this->isList($value)) {
            if ($parentKey === 'relations') {
                return $this->normalizeRelationsListForCompare($value);
            }

            if ($parentKey === 'references') {
                return $this->normalizeReferencesListForCompare($value);
            }

            return array_map(fn ($item) => $this->normalizeCompareValue($item), $value);
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            $normalized[$key] = $this->normalizeCompareValue($item, is_string($key) ? $key : null);
        }

        ksort($normalized);

        return $normalized;
    }

    private function normalizeRelationsListForCompare(array $relations): array
    {
        $normalized = [];

        foreach ($relations as $index => $relation) {
            if (!is_array($relation)) {
                $normalized['__index_'.$index] = $this->normalizeCompareValue($relation);
                continue;
            }

            $relationId = $relation['relationId'] ?? null;
            if (is_array($relationId)) {
                $relationId = end($relationId) ?: reset($relationId);
            }

            $key = is_scalar($relationId) && (string) $relationId !== '' ? (string) $relationId : '__index_'.$index;
            $normalized[$key] = $this->normalizeCompareValue($relation);
        }

        ksort($normalized);

        return $normalized;
    }

    private function normalizeReferencesListForCompare(array $references): array
    {
        $normalized = [];

        foreach ($references as $index => $reference) {
            if (!is_array($reference)) {
                $normalized['__index_'.$index] = $this->normalizeCompareValue($reference);
                continue;
            }

            $refId = $reference['$id'] ?? $reference['_$id'] ?? null;
            if (is_array($refId)) {
                $refId = end($refId) ?: reset($refId);
            }

            $refClass = $reference['class'] ?? null;
            if (is_array($refClass)) {
                $refClass = end($refClass) ?: reset($refClass);
            }

            $key = '';
            if (is_scalar($refClass) && (string) $refClass !== '') {
                $key .= (string) $refClass;
            }
            if (is_scalar($refId) && (string) $refId !== '') {
                $key .= '#'.(string) $refId;
            }
            if ($key === '') {
                $key = '__index_'.$index;
            }

            $normalized[$key] = $this->normalizeCompareValue($reference);
        }

        ksort($normalized);

        return $normalized;
    }

    private function isList(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    private function buildHistoryQuery(string $contentId, array $filters): MongoQueryBuilder
    {
        $builder = $this->manager->getRepository(ContentHistory::class)->createQueryBuilder();
        $builder->field('contentId')->equals($contentId);

        if ($filters['action'] !== '') {
            $builder->field('action')->equals($filters['action']);
        }

        if ($filters['user'] !== '') {
            $builder->field('user.name')->equals($this->buildCaseInsensitiveRegex($filters['user']));
        }

        if ($filters['q'] !== '') {
            $regex = $this->buildCaseInsensitiveRegex($filters['q']);
            $builder->addOr($builder->expr()->field('action')->equals($regex));
            $builder->addOr($builder->expr()->field('user.name')->equals($regex));
            $builder->addOr($builder->expr()->field('user.id')->equals($regex));
        }

        if ($filters['from'] !== '') {
            try {
                $from = new \DateTimeImmutable($filters['from'].' 00:00:00');
                $builder->field('date')->gte($from);
            } catch (\Exception) {
            }
        }

        if ($filters['to'] !== '') {
            try {
                $to = new \DateTimeImmutable($filters['to'].' 23:59:59');
                $builder->field('date')->lte($to);
            } catch (\Exception) {
            }
        }

        $builder->sort('date', 'desc');

        return $builder;
    }

    private function buildCaseInsensitiveRegex(string $query)
    {
        $pattern = preg_quote($query, '/');

        if (class_exists(\MongoDB\BSON\Regex::class)) {
            return new \MongoDB\BSON\Regex($pattern, 'i');
        }

        return new \MongoRegex('/'.$pattern.'/i');
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
