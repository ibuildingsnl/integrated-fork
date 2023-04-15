<?php

namespace Integrated\Bundle\TaxonomyBundle\Domain;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;

interface TaxonomyRepositoryInterface
{
    /** @return Taxonomy[] */
    public function all(): array;

    /** @return Taxonomy[] */
    public function paged(string $contentType, int $page, int $pageSize): array;

    public function byId(string $id): ?Taxonomy;

    /** @return Taxonomy[] */
    public function byType(string $contentType): array;

    public function count(string $contentType): int;

    public function add(Taxonomy $taxonomy): void;

    public function countUsages(Taxonomy $taxonomy): int;
}
