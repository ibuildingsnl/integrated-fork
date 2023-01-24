<?php

namespace Integrated\Common\Solr\Search\Type;

interface RegistryInterface
{
    public function hasType(string $name): bool;

    public function getType(string $name): ResolvedTypeInterface;
}
