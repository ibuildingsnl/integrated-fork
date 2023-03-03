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

use Solarium\QueryType\Select\Query\Query as SolariumQuery;

class Query
{
    private SolariumQuery $query;
    private array $options;

    public function __construct(SolariumQuery $query, array $options)
    {
        $this->query = $query;
        $this->options = $options;
    }

    public function getQuery(): SolariumQuery
    {
        return $this->query;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
