<?php

declare(strict_types=1);

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\TaxonomyBundle\EventListener\TaxonomyParentRelationListener;
use Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles\MemoryTaxonomyRepository;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Form\Mapping\Metadata\Document;
use PHPUnit\Framework\TestCase;

final class TaxonomyParentRelationListenerTest extends TestCase
{
    public function testSwitchingParentMovesChildReferenceAndParentRelation(): void
    {
        $repository = new MemoryTaxonomyRepository();
        $parentA = $this->taxonomy('parent-a');
        $parentB = $this->taxonomy('parent-b');
        $child = $this->taxonomy('child', 'parent-b');

        $repository->add($parentA);
        $repository->add($parentB);

        $child->addRelation(
            (new Relation())
                ->setRelationId('__parent')
                ->setRelationType('embedded')
                ->addReference($parentA)
        );
        $parentA->addRelation(
            (new Relation())
                ->setRelationId('__children')
                ->setRelationType('embedded')
                ->addReference($child)
        );

        $listener = new TestTaxonomyParentRelationListener($repository, $this->createMock(DocumentManager::class));
        $listener->setParentsReferencing([$parentA, $parentB]);
        $listener->afterValidation($this->event($child));

        self::assertSame(['parent-b'], $this->relationReferenceIds($child, '__parent'));
        self::assertSame([], $this->relationReferenceIds($parentA, '__children'));
        self::assertSame(['child'], $this->relationReferenceIds($parentB, '__children'));
    }

    public function testRepeatedSaveDeduplicatesParentAndChildrenRelations(): void
    {
        $repository = new MemoryTaxonomyRepository();
        $parent = $this->taxonomy('parent');
        $parentDuplicate = $this->taxonomy('parent');
        $child = $this->taxonomy('child', 'parent');
        $childDuplicate = $this->taxonomy('child');

        $repository->add($parent);

        $child->addRelation(
            (new Relation())
                ->setRelationId('__parent')
                ->setRelationType('embedded')
                ->addReference($parent)
                ->addReference($parentDuplicate)
        );
        $parent->addRelation(
            (new Relation())
                ->setRelationId('__children')
                ->setRelationType('embedded')
                ->addReference($child)
                ->addReference($childDuplicate)
        );

        $listener = new TestTaxonomyParentRelationListener($repository, $this->createMock(DocumentManager::class));
        $listener->setParentsReferencing([$parent]);
        $listener->afterValidation($this->event($child));
        $listener->afterValidation($this->event($child));

        self::assertSame(['parent'], $this->relationReferenceIds($child, '__parent'));
        self::assertSame(['child'], $this->relationReferenceIds($parent, '__children'));
    }

    /** @return list<string> */
    private function relationReferenceIds(Taxonomy $taxonomy, string $relationId): array
    {
        $relation = $taxonomy->getRelation($relationId);
        if (!$relation instanceof Relation) {
            return [];
        }

        return array_values(array_filter(array_map(static function ($reference): string {
            return trim((string) $reference->getId());
        }, $relation->getReferences()), static fn (string $id): bool => '' !== $id));
    }

    private function taxonomy(string $id, ?string $parentId = null): Taxonomy
    {
        $taxonomy = new Taxonomy();
        $taxonomy->setId($id);
        $taxonomy->setParentID($parentId);
        $taxonomy->setContentType('taxonomy');

        return $taxonomy;
    }

    private function event(Taxonomy $taxonomy): ValidationEvent
    {
        return new ValidationEvent(
            (new ContentType())->setId('taxonomy')->setClass(Taxonomy::class),
            new Document(Taxonomy::class),
            $taxonomy,
        );
    }
}

final class TestTaxonomyParentRelationListener extends TaxonomyParentRelationListener
{
    /** @var Taxonomy[] */
    private array $parentsReferencing = [];

    /** @param Taxonomy[] $parents */
    public function setParentsReferencing(array $parents): void
    {
        $this->parentsReferencing = $parents;
    }

    /** @return iterable<int, Taxonomy> */
    protected function parentsReferencingTaxonomy(Taxonomy $taxonomy): iterable
    {
        return $this->parentsReferencing;
    }
}
