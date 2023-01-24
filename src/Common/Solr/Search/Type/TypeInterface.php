<?php

namespace Integrated\Common\Solr\Search\Type;

use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface TypeInterface
{
    public function build(Query $query, array $options): void;

    public function setOptions(OptionsResolver $resolver): void;

    public function getParent(): ?string;
}
