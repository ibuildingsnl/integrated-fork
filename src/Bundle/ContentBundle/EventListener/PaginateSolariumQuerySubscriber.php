<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\EventListener;

use Knp\Component\Pager\Event\ItemsEvent;
use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Solarium query pagination (to support max items).
 */
class PaginateSolariumQuerySubscriber implements EventSubscriberInterface
{
    public function items(ItemsEvent $event)
    {
        if (!\is_array($event->target) || 2 !== \count($event->target)) {
            return;
        }

        [$client, $query] = array_values($event->target);

        if (!$client instanceof Client || !$query instanceof Query) {
            return;
        }

        $offset = max(0, (int) $event->getOffset());
        $limit = max(1, (int) $event->getLimit());
        $maxItems = (int) ($event->options['maxItems'] ?? 0);

        if ($maxItems > 0) {
            if ($offset >= $maxItems) {
                $event->items = [];
                $event->count = $maxItems;
                $event->stopPropagation();

                return;
            }

            $remaining = $maxItems - $offset;
            if ($limit > $remaining) {
                $limit = $remaining;
            }
        }

        $result = $client->select($query->setStart($offset)->setRows($limit));

        $event->items = $result->getDocuments();
        $event->count = $maxItems > 0 ? min($maxItems, $result->getNumFound()) : $result->getNumFound();

        $event->setCustomPaginationParameter('result', $result);
        $event->stopPropagation();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'knp_pager.items' => ['items', 1],
        ];
    }
}
