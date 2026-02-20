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
                'old' => $this->normalizeValue($data[0]),
                'new' => $this->normalizeValue($data[1]),
            ];

            return;
        }

        foreach ($data as $key => $subdata) {
            $subdata = $this->normalizeValue($subdata);
            if (!\is_array($subdata)) {
                $table[] = [
                    'name' => $path.' > '.$key,
                    'old' => '',
                    'new' => $subdata,
                ];

                continue;
            }

            $this->walkArray($table, $path.' > '.$key, $subdata);
        }
    }

    private function normalizeValue($value)
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

            $formatted = $this->formatReferenceArray($value);
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
                $formattedItems = [];
                foreach ($value as $item) {
                    $formattedItem = $this->formatReferenceArray(\is_array($item) ? $this->normalizeArrayKeys($item) : []);
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

    private function formatReferenceArray(array $value): ?string
    {
        $refId = $value['$id'] ?? $value['id'] ?? null;
        $refClass = $value['class'] ?? null;
        $refCollection = $value['$ref'] ?? null;

        if (\is_array($refId)) {
            $refId = $refId[1] ?? $refId[0] ?? null;
        }
        if (\is_array($refClass)) {
            $refClass = $refClass[1] ?? $refClass[0] ?? null;
        }
        if (\is_array($refCollection)) {
            $refCollection = $refCollection[1] ?? $refCollection[0] ?? null;
        }

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
}
