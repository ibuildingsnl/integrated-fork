<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Services;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class AssignedStatusCacheInvalidator
{
    public const CACHE_NAMESPACE = 'integrated_content_assigned_status_v2';

    public static function getCacheItemKey(string $userId): string
    {
        return 'assigned_status_'.md5($userId);
    }

    /**
     * @param array<int, string|null> $userIds
     */
    public function invalidateUsers(array $userIds): void
    {
        $keys = [];

        foreach ($userIds as $userId) {
            if (!\is_string($userId) || '' === $userId) {
                continue;
            }

            $keys[] = self::getCacheItemKey($userId);
        }

        if ([] === $keys) {
            return;
        }

        $cache = new FilesystemAdapter(self::CACHE_NAMESPACE);
        $cache->deleteItems(array_values(array_unique($keys)));
    }
}
