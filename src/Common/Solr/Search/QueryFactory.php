<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Search;

use Integrated\Common\Solr\Search\Event\CreateEvent;
use Integrated\Common\Solr\Search\Event\PostCreateEvent;
use Integrated\Common\Solr\Search\Event\PreCreateEvent;
use Integrated\Common\Solr\Search\Type\RegistryInterface;
use Solarium\QueryType\Select\Query\Query as SolariumQuery;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class QueryFactory implements QueryFactoryInterface
{
    private RegistryInterface $registry;
    private EventDispatcherInterface $dispatcher;

    public function __construct(RegistryInterface $registry, EventDispatcherInterface $dispatcher)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
    }

    public function createQuery(string $type, array $options): Query
    {
        $type = $this->registry->getType($type);

        // remove values from the option that are not defined in the option resolver so that no
        // undefined options exception will be thrown

        $resolver = $type->getOptions();

        foreach ($options as $key => $value) {
            if (!$resolver->isDefined($key)) {
                unset($options[$key]);
            }
        }

        $options = $resolver->resolve($options);

        // allow an event listener to override the factory by returning a select query instead.
        $this->dispatcher->dispatch($event = new PreCreateEvent($type->getType()::class, $options), QueryEvents::PRE_CREATE);

        if ($event->getQuery()) {
            return new Query($event->getQuery(), $options);
        }

        $this->dispatcher->dispatch($event = new CreateEvent($type->getType()::class, $options), QueryEvents::CREATE);

        $query = $event->getQuery();

        if (!$query) {
            $query = new SolariumQuery();
        }

        $type->build($query, $options);

        $this->dispatcher->dispatch($event = new PostCreateEvent($type->getType()::class, $options, $query), QueryEvents::POST_CREATE);

        return new Query($event->getQuery(), $options);
    }
}
