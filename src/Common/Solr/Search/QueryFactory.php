<?php

namespace Integrated\Common\Solr\Search;

use Integrated\Common\Solr\Search\Type\RegistryInterface;
use Solarium\QueryType\Select\Query\Query;

class QueryFactory implements QueryFactoryInterface
{
    private RegistryInterface $registry;

    public function getQuery(string $type, array $options): Query
    {
        // pre event that can return a query

        $type = $this->registry->getType($type);

        $resolver = $type->getOptions();

        foreach ($options as $key => $value) {
            if (!$resolver->isDefined($key)) {
                unset($options[$key]);
            }
        }

        $options = $resolver->resolve($options);

        // pick one of the 3
        // - the first one does not have any client dependencies and will not fire events
        // - the second one will fire solarium query creation eventsbc
        // - the third option would be to just make a factory that will return a query object and
        //   have that solve the problem of who will create a query intance

        $query = new Query();
        $query = $this->client->createSelect();
        $query = $this->factory->create();

        $type->build($query, $options);

        // post event that can modify the query

        return $query;
    }
}
