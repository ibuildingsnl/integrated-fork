<?php

namespace Integrated\Common\Solr\Search\Type;

interface ResolvedTypeFactoryInterface
{
    /**
     * @param TypeExtensionInterface[] $extensions
     */
    public function create(TypeInterface $type, array $extensions, ResolvedTypeInterface $parent = null): ResolvedTypeInterface;
}
