<?php

namespace Integrated\Common\Solr\Search;

use Solarium\QueryType\Select\Query\Query;

interface QueryFactoryInterface
{
    /**
     * create a select query.
     *
     * $query = $this->getQuery(QueryType::class, ['option1' => 'value1', 'option2' => 'value2']);
     * $result = $client->select($query);
     *
     * ---
     *
     * $query = $this->getQuery(QueryType::class, ['option1' => 'value1', 'option2' => 'value2']);
     * $paginator = $paginator->paginate([$client, $query], $page, $limit);
     */
    public function getQuery(string $type, array $options): Query;
}
