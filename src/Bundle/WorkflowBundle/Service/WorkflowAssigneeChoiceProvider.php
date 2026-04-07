<?php

namespace Integrated\Bundle\WorkflowBundle\Service;

use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Psr\Cache\CacheItemPoolInterface;

class WorkflowAssigneeChoiceProvider
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getChoices(): array
    {
        $item = $this->cache->getItem(WorkflowAssigneeChoiceCacheInvalidator::CACHE_ITEM_KEY);
        if ($item->isHit()) {
            $value = $item->get();

            return \is_array($value) ? $value : [];
        }

        $builder = $this->userManager->createQueryBuilder();
        $builder->join('User.scope', 'us');
        $builder->where('us.admin = 1');

        $users = [];

        foreach ($builder->getQuery()->getArrayResult() as $row) {
            if (!\is_array($row) || !isset($row['username'], $row['id'])) {
                continue;
            }

            $users[(string) $row['username']] = $row['id'];
        }

        $item->set($users);
        $this->cache->save($item);

        return $users;
    }
}
