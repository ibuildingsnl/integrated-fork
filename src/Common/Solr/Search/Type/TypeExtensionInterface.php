<?php

namespace Integrated\Common\Solr\Search\Type;

use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface TypeExtensionInterface
{
    public function build(Query $query, array $options): void;

    public function setOptions(OptionsResolver $resolver): void;

    /**
     * @return string[]
     */
    public function getTypes(): iterable;
}
