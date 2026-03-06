<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Integrated\Common\ContentType\ResolverInterface;

final class TaxonomyViewer implements TaxonomyOverview
{
    public function __construct(
        private readonly ResolverInterface $types,
        private readonly TaxonomyIndexer $indexer,
        private readonly TaxonomyLister $lister,
    ) {
    }

    public function childrenOf(string $contentType, string $parentId): array
    {
        return $this->hasParents($contentType) ?
            $this->indexer->childrenOf($contentType, $parentId) :
            $this->lister->childrenOf($contentType, $parentId);
    }

    public function overviewFor(string $contentType, ?TaxonomyOptions $options = null): array
    {
        return $this->hasParents($contentType) ?
            $this->indexer->overviewFor($contentType, $options) :
            $this->lister->overviewFor($contentType, $options);
    }

    public function countFor(string $contentType, string $filter = 'root'): int
    {
        return $this->hasParents($contentType) ?
            $this->indexer->countFor($contentType, $filter) :
            $this->lister->countFor($contentType, $filter);
    }

    private function hasParents(string $contentType): bool
    {
        return $this->types->getType($contentType)->hasField('parent_id');
    }
}
