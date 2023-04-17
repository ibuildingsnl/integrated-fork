<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class MemoryTaxonomyRepository implements TaxonomyRepositoryInterface
{
    /** @var Taxonomy[] */
    private array $taxonomies = [];
    /** @var int[] */
    private array $usages = [];

    public function __construct(
        private readonly ?AuthorizationCheckerInterface $authorization = null,
    ) {
    }

    public function all(): array
    {
        return $this->taxonomies;
    }

    public function paged(string $contentType, int $offset, int $limit): array
    {
        $items = array_filter(
            $this->byType($contentType),
            fn (Taxonomy $t) => $this->authorization?->isGranted('view', $t) ?? true
        );
        usort(
            $items,
            fn (Taxonomy $a, Taxonomy $b) => $a->getRank() !== $b->getRank() ?
                $a->getRank() <=> $b->getRank() :
                $a->getTitle() <=> $b->getTitle()
        );

        return \array_slice($items, $offset, $limit);
    }

    public function byId(string $id): ?Taxonomy
    {
        foreach ($this->taxonomies as $taxonomy) {
            if ($taxonomy->getId() === $id) {
                return $taxonomy;
            }
        }

        return null;
    }

    public function byType(string $contentType): array
    {
        return array_filter($this->taxonomies, fn (Taxonomy $t) => $t->getContentType() === $contentType);
    }

    public function count(string $contentType): int
    {
        return \count($this->byType($contentType));
    }

    public function add(Taxonomy $taxonomy): void
    {
        $this->taxonomies[] = $taxonomy;
    }

    public function countUsages(Taxonomy $taxonomy): int
    {
        return $this->usages[$taxonomy->getId()] ?? 0;
    }

    public function setUsageCount(string $taxonomyId, int $usages): void
    {
        $this->usages[$taxonomyId] = $usages;
    }
}
