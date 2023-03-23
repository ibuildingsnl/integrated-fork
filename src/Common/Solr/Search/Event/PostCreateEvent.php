<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Search\Event;

use Solarium\QueryType\Select\Query\Query;
use Symfony\Contracts\EventDispatcher\Event;

class PostCreateEvent extends Event
{
    private string $type;
    private array $options;
    private Query $query;

    public function __construct(string $type, array $options, Query $query)
    {
        $this->type = $type;
        $this->options = $options;
        $this->query = $query;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    public function setQuery(Query $query): void
    {
        $this->query = $query;
    }
}
