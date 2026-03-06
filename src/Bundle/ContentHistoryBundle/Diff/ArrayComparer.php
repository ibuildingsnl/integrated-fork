<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentHistoryBundle\Diff;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class ArrayComparer
{
    public const IGNORE_KEYS = ['$db'];

    /**
     * @param array $old
     * @param array $new
     *
     * @return array
     */
    public static function diff($old, $new)
    {
        $oldOriginal = $old;
        $newOriginal = $new;

        $old = self::normalizeValue($old);
        $new = self::normalizeValue($new);

        if (!\is_array($old) && !\is_array($new)) {
            if (self::isSame($old, $new)) {
                return [];
            }

            return [$oldOriginal, $newOriginal];
        }

        if (!\is_array($old)) {
            $old = [0 => $old];
        }

        if (!\is_array($new)) {
            $new = [0 => $new];
        }

        $old = self::normalizeArrays($new, $old);
        $new = self::normalizeArrays($old, $new);

        $diff = [];

        foreach ($new as $key => $value) {
            if (\in_array($key, self::IGNORE_KEYS, true)) {
                continue;
            }

            $result = self::diff($old[$key], $value);
            if (\count($result)) {
                $diff[$key] = $result;

                if ($key === 'references') {
                    if (isset($old['relationId'])) {
                        $diff['relationId'] = [$old['relationId'], $old['relationId']];
                    } elseif (isset($new['relationId'])) {
                        $diff['relationId'] = [$new['relationId'], $new['relationId']];
                    }
                }
            }
        }

        return $diff;
    }

    /**
     * @return array
     */
    public static function normalizeArrays(array $old = [], array $new = [])
    {
        foreach (array_keys($old) as $key) {
            if (!\array_key_exists($key, $new)) {
                // add missing key
                if (\is_array($old[$key])) {
                    $new[$key] = [];
                } else {
                    $new[$key] = null;
                }
            }

            if (\is_array($old[$key]) && !\is_array($new[$key])) {
                $new[$key] = [];
            }
        }

        return $new;
    }

    public static function normalizeValue($value, bool $allowArray = true)
    {
        if (\is_object($value)) {
            $value = serialize((array) $value);
        }

        if ($allowArray && \is_array($value)) {
            if (isset($value['relations']) && \is_array($value['relations'])) {
                $value['relations'] = self::normalizeRelationsList($value['relations']);
            }

            foreach ($value as $key => $item) {
                if (substr($key, 0, 1) === '$') {
                    $value['_'.$key] = $item;
                    unset($value[$key]);
                }
            }
        }

        if (!$allowArray && \is_array($value)) {
            $value = serialize($value);
        }

        return $value;
    }

    private static function normalizeRelationsList(array $relations): array
    {
        $normalized = [];
        foreach ($relations as $index => $relation) {
            if (!\is_array($relation)) {
                $normalized['__index_'.$index] = $relation;
                continue;
            }

            $relationId = self::pickScalar($relation['relationId'] ?? null);
            $key = (\is_string($relationId) && $relationId !== '') ? $relationId : '__index_'.$index;

            if (isset($relation['references']) && \is_array($relation['references'])) {
                $relation['references'] = self::normalizeReferencesList($relation['references']);
            }

            $normalized[$key] = $relation;
        }

        ksort($normalized);

        return $normalized;
    }

    private static function normalizeReferencesList(array $references): array
    {
        $normalized = [];
        foreach ($references as $index => $reference) {
            if (!\is_array($reference)) {
                $normalized['__index_'.$index] = $reference;
                continue;
            }

            $refClass = self::pickScalar($reference['class'] ?? null);
            $refId = self::pickScalar($reference['_$id'] ?? ($reference['$id'] ?? null));

            $key = '';
            if (\is_string($refClass) && $refClass !== '' && \is_string($refId) && $refId !== '') {
                $key = $refClass.'#'.$refId;
            } elseif (\is_string($refId) && $refId !== '') {
                $key = '#'.$refId;
            } else {
                $key = '__index_'.$index;
            }

            $normalized[$key] = $reference;
        }

        ksort($normalized);

        return $normalized;
    }

    private static function pickScalar(mixed $value): ?string
    {
        if (\is_scalar($value) && (string) $value !== '') {
            return (string) $value;
        }

        if (!\is_array($value)) {
            return null;
        }

        foreach ($value as $item) {
            if (\is_scalar($item) && (string) $item !== '') {
                return (string) $item;
            }
        }

        return null;
    }

    public static function isSame($value1, $value2): bool
    {
        $value1 = (string) self::normalizeValue($value1, false);
        $value2 = (string) self::normalizeValue($value2, false);

        return $value1 === $value2;
    }
}
