<?php

namespace Integrated\Bundle\WorkflowBundle\Service;

use Psr\Cache\CacheItemPoolInterface;

class WorkflowAssigneeChoiceCacheInvalidator
{
    public const CACHE_ITEM_KEY = 'workflow_assignee_choices_admin_scope';

    public function __construct(private readonly CacheItemPoolInterface $cache)
    {
    }

    public function invalidate(): void
    {
        $this->cache->deleteItem(self::CACHE_ITEM_KEY);
    }
}
