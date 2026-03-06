<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentHistoryBundle\History;

use Integrated\Bundle\ContentHistoryBundle\Document\ContentHistory;

class Parser
{
    public function getReadableChangeset(ContentHistory $history): array
    {
        return $this->getReadableChangesetFromArray($history->getChangeSet());
    }

    public function getReadableChangesetFromArray(array $changeSet): array
    {
        $table = [];

        foreach ($changeSet as $key => $data) {
            $data = $this->normalizeValue($data);

            if (!\is_array($data)) {
                $table[] = [
                    'name' => $key,
                    'old' => '',
                    'new' => $data,
                ];

                continue;
            }

            $this->walkArray($table, $key, $data);
        }

        return $table;
    }

    private function walkArray(array &$table, string $path, array $data)
    {
        if (\count($data) === 2 && \array_key_exists(0, $data) && \array_key_exists(1, $data) && !\is_array($data[0]) && !\is_array($data[1])) {
            $table[] = [
                'name' => $path,
                'old' => $this->normalizeValueByPath($path, $data[0], false),
                'new' => $this->normalizeValueByPath($path, $data[1], true),
            ];

            return;
        }

        foreach ($data as $key => $subdata) {
            $subdata = $this->normalizeValue($subdata);
            if (!\is_array($subdata)) {
                $table[] = [
                    'name' => $path.' > '.$key,
                    'old' => '',
                    'new' => $this->normalizeValueByPath($path.' > '.$key, $subdata, true),
                ];

                continue;
            }

            $this->walkArray($table, $path.' > '.$key, $subdata);
        }
    }

    private function normalizeValue($value, bool $preferNew = true)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('r');
        }

        if ($value instanceof \MongoDate) {
            return $value->toDateTime()->format('r');
        }

        if (\is_string($value)) {
            $decoded = $this->tryUnserialize($value);
            if ($decoded !== null) {
                $value = $decoded;
            } else {
                $json = $this->tryJsonDecode($value);
                if ($json !== null) {
                    $value = $json;
                }
            }
        }

        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (\is_array($value)) {
            $value = $this->normalizeArrayKeys($value);

            $tupleFormatted = $this->formatRelationTupleValue($value);
            if ($tupleFormatted !== null) {
                return $tupleFormatted;
            }

            $formattedRelation = $this->formatRelationPayload($value, $preferNew);
            if ($formattedRelation !== null) {
                return $formattedRelation;
            }

            $formatted = $this->formatReferenceArray($value, $preferNew);
            if ($formatted !== null) {
                return $formatted;
            }

            if ($this->isMillisArray($value)) {
                return $this->formatMillisArray($value);
            }

            // Keep [old, new] tuples intact so walkArray can render proper old/new columns.
            if (\count($value) === 2 && \array_key_exists(0, $value) && \array_key_exists(1, $value)
                && !\is_array($value[0]) && !\is_array($value[1])) {
                return $value;
            }

            if ($this->isList($value)) {
                $formattedRelationList = $this->formatRelationPayloadList($value, $preferNew);
                if ($formattedRelationList !== null) {
                    return $formattedRelationList;
                }

                $formattedItems = [];
                foreach ($value as $item) {
                    $normalizedItem = \is_array($item) ? $this->normalizeArrayKeys($item) : [];
                    $formattedItem = $this->formatRelationPayload($normalizedItem, $preferNew);
                    if ($formattedItem !== null) {
                        $formattedItems[] = $formattedItem;
                        continue;
                    }

                    $formattedItem = $this->formatReferenceArray($normalizedItem, $preferNew);
                    if ($formattedItem !== null) {
                        $formattedItems[] = $formattedItem;
                        continue;
                    }
                    $formattedItems[] = $item;
                }
                if (\count($formattedItems)) {
                    return implode(', ', array_map([$this, 'stringifyScalar'], $formattedItems));
                }
            }
        }

        return $value;
    }

    private function formatRelationTupleValue(array $value): ?array
    {
        if (!$this->isRelationPayloadShape($value) && !$this->isRelationPayloadListShape($value)) {
            return null;
        }

        if (!$this->containsTwoValueTuple($value)) {
            return null;
        }

        $oldValue = $this->resolveTupleSide($value, false);
        $newValue = $this->resolveTupleSide($value, true);

        if (!\is_array($oldValue) || !\is_array($newValue)) {
            return null;
        }

        if ($this->isRelationPayloadShape($oldValue) && $this->isRelationPayloadShape($newValue)) {
            return [
                (string) ($this->formatRelationPayload($oldValue, false) ?? ''),
                (string) ($this->formatRelationPayload($newValue, true) ?? ''),
            ];
        }

        if ($this->isRelationPayloadListShape($oldValue) && $this->isRelationPayloadListShape($newValue)) {
            return [
                (string) ($this->formatRelationPayloadList($oldValue, false) ?? ''),
                (string) ($this->formatRelationPayloadList($newValue, true) ?? ''),
            ];
        }

        return null;
    }

    private function isRelationPayloadShape(array $value): bool
    {
        return isset($value['relationId']) && isset($value['references']);
    }

    private function isRelationPayloadListShape(array $value): bool
    {
        if ($value === [] || !$this->isList($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!\is_array($item)) {
                return false;
            }

            $item = $this->normalizeArrayKeys($item);
            if (!$this->isRelationPayloadShape($item)) {
                return false;
            }
        }

        return true;
    }

    private function containsTwoValueTuple($value): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        if (\count($value) === 2 && \array_key_exists(0, $value) && \array_key_exists(1, $value)
            && !\is_array($value[0]) && !\is_array($value[1])) {
            return true;
        }

        foreach ($value as $item) {
            if ($this->containsTwoValueTuple($item)) {
                return true;
            }
        }

        return false;
    }

    private function resolveTupleSide($value, bool $preferNew = true)
    {
        if (!\is_array($value)) {
            return $value;
        }

        if (\count($value) === 2 && \array_key_exists(0, $value) && \array_key_exists(1, $value)
            && !\is_array($value[0]) && !\is_array($value[1])) {
            return $preferNew ? $value[1] : $value[0];
        }

        $resolved = [];
        foreach ($value as $key => $item) {
            $resolved[$key] = $this->resolveTupleSide($item, $preferNew);
        }

        return $resolved;
    }

    private function normalizeValueByPath(string $path, $value, bool $preferNew = true)
    {
        $normalized = $this->normalizeValue($value, $preferNew);

        if (\is_string($normalized) && $this->isRelationIdPath($path)) {
            return $this->humanizeRelationId($normalized);
        }

        return $normalized;
    }

    private function tryUnserialize(string $value): mixed
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (!preg_match('/^(a|O|s|b|i|d):/', $value)) {
            return null;
        }

        $decoded = @unserialize($value);
        if ($decoded === false && $value !== 'b:0;') {
            return null;
        }

        return $decoded;
    }

    private function tryJsonDecode(string $value): mixed
    {
        $value = trim($value);
        if ($value === '' || ($value[0] !== '{' && $value[0] !== '[')) {
            return null;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    private function normalizeArrayKeys(array $value): array
    {
        $normalized = [];
        foreach ($value as $key => $item) {
            if (\is_string($key) && str_starts_with($key, '_$')) {
                $normalized[substr($key, 1)] = $item;
            } else {
                $normalized[$key] = $item;
            }
        }

        return $normalized;
    }

    private function formatReferenceArray(array $value, bool $preferNew = true): ?string
    {
        $refId = $value['$id'] ?? $value['id'] ?? null;
        $refClass = $value['class'] ?? null;
        $refCollection = $value['$ref'] ?? null;

        $refId = $this->pickPreferredScalar($refId, $preferNew);
        $refClass = $this->pickPreferredScalar($refClass, $preferNew);
        $refCollection = $this->pickPreferredScalar($refCollection, $preferNew);

        if (!$refId && !$refClass && !$refCollection) {
            return null;
        }

        $parts = [];
        if ($refClass) {
            $parts[] = $refClass;
        } elseif ($refCollection) {
            $parts[] = $refCollection;
        } else {
            $parts[] = 'Reference';
        }

        if ($refId) {
            $parts[] = '#' . $refId;
        }

        return implode(' ', $parts);
    }

    private function isMillisArray(array $value): bool
    {
        if (!isset($value['milliseconds'])) {
            return false;
        }

        return is_numeric($value['milliseconds']);
    }

    private function formatMillisArray(array $value): string
    {
        $millis = (int) $value['milliseconds'];
        $seconds = (int) floor($millis / 1000);

        try {
            $dt = (new \DateTimeImmutable())->setTimestamp($seconds);
            return $dt->format('r');
        } catch (\Exception $e) {
            return (string) $millis;
        }
    }

    private function isList(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        $keys = array_keys($value);
        return $keys === range(0, count($value) - 1);
    }

    private function stringifyScalar($value): string
    {
        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (\is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value);
    }

    private function formatRelationPayloadList(array $items, bool $preferNew = true): ?string
    {
        if ($items === []) {
            return null;
        }

        $relationGroups = [];

        foreach ($items as $item) {
            if (!\is_array($item)) {
                return null;
            }

            $item = $this->normalizeArrayKeys($item);
            if (!isset($item['relationId']) || !isset($item['references']) || !\is_array($item['references'])) {
                return null;
            }

            $itemRelationId = $this->pickPreferredScalar($item['relationId'], $preferNew);
            if ($itemRelationId === null || $itemRelationId === '') {
                return null;
            }

            if (!isset($relationGroups[$itemRelationId])) {
                $relationGroups[$itemRelationId] = [];
            }

            foreach ($item['references'] as $reference) {
                if (!\is_array($reference)) {
                    continue;
                }

                $reference = $this->normalizeArrayKeys($reference);
                $formatted = $this->formatReferenceArray($reference, $preferNew);
                if ($formatted !== null) {
                    $relationGroups[$itemRelationId][] = $formatted;
                }
            }
        }

        if ($relationGroups === []) {
            return null;
        }

        $parts = [];
        foreach ($relationGroups as $relationId => $labels) {
            $labels = array_values(array_unique($labels));
            $relationLabel = $this->humanizeRelationId((string) $relationId);

            if ($labels === []) {
                $parts[] = sprintf('%s: none', $relationLabel);
                continue;
            }

            $parts[] = sprintf('%s: %s', $relationLabel, implode(', ', $labels));
        }

        return implode(', ', $parts);
    }

    private function formatRelationPayload(array $value, bool $preferNew = true): ?string
    {
        if (!isset($value['relationId']) || !isset($value['references'])) {
            return null;
        }

        $relationId = $this->pickPreferredScalar($value['relationId'], $preferNew);
        $relationLabel = $relationId ? $this->humanizeRelationId($relationId) : 'Relation';
        $references = $value['references'];

        if (!\is_array($references) || $references === []) {
            return sprintf('%s: none', $relationLabel);
        }

        $labels = [];
        foreach ($references as $reference) {
            if (!\is_array($reference)) {
                continue;
            }

            $reference = $this->normalizeArrayKeys($reference);
            $formatted = $this->formatReferenceArray($reference, $preferNew);
            if ($formatted !== null) {
                $labels[] = $formatted;
            }
        }

        if ($labels === []) {
            return sprintf('%s: none', $relationLabel);
        }

        return sprintf('%s: %s', $relationLabel, implode(', ', $labels));
    }

    private function pickPreferredScalar($value, bool $preferNew = true): ?string
    {
        if (\is_scalar($value) && (string) $value !== '') {
            return (string) $value;
        }

        if (!\is_array($value)) {
            return null;
        }

        if ($preferNew) {
            foreach (array_reverse($value, true) as $item) {
                if (\is_scalar($item) && (string) $item !== '') {
                    return (string) $item;
                }
            }
        } else {
            foreach ($value as $item) {
                if (\is_scalar($item) && (string) $item !== '') {
                    return (string) $item;
                }
            }
        }

        return null;
    }

    private function isRelationIdPath(string $path): bool
    {
        $path = strtolower(trim($path));

        return $path === 'relationid' || str_ends_with($path, ' > relationid');
    }

    private function humanizeRelationId(string $relationId): string
    {
        $relationId = ltrim($relationId, '_');
        if ($relationId === '') {
            return '';
        }

        $relationId = str_replace(['-', '_'], ' ', $relationId);

        return ucfirst(trim($relationId));
    }
}
