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
    const IGNORE_KEYS = ['$db'];

    /**
     * @param array $old
     * @param array $new
     *
     * @return array
     */
    public static function diff(array $old = [], array $new = [])
    {
        $diff = array_merge_recursive(self::compareOld($old, $new), self::compareNew($new, $old));

        return $diff;
    }


    public static function compareOld(array $old, array $new)
    {
        $diff = [];
        foreach ($old as $key => $value) {
            if (array_key_exists($key, $new)) {
                if (is_array($value) && is_array($new[$key]) && $key != 'relations') {
                    if ($diffSub = self::compareOld($value, $new[$key])) {
                        $diff[$key] = $diffSub;
                    }
                }
            } else {
                if (!empty($value)) {
                    $diff[$key] = [$value, null];
                }
            }
        }

        return $diff;
    }

    public static function compareNew(array $new, array $old)
    {
        $diff = [];
        foreach ($new as $key => $value) {
            if (array_key_exists($key, $old)) {
                if (is_array($value) && is_array($old[$key]) && $key !== 'relations') {
                    if ($diffSub = self::compareNew($value, $old[$key])) {
                        $diff[$key] = $diffSub;
                    }
                } else {
                    if ($value != $old[$key]) {
                        $diff[$key] = [$old[$key], $value];
                    }
                }
            } else {
                if (!empty($value)) {
                    $diff[$key] = [null, $value];
                }
            }
        }


        return $diff;
    }
}
