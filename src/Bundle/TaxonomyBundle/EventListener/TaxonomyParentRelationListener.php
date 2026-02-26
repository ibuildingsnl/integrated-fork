<?php

namespace Integrated\Bundle\TaxonomyBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TaxonomyParentRelationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly TaxonomyRepositoryInterface $taxonomies,
        private readonly DocumentManager $documentManger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_VALIDATE => 'afterValidation',
        ];
    }

    public function afterValidation(ValidationEvent $event): void
    {
        $taxonomy = $event->getContent();

        if (!$taxonomy instanceof Taxonomy) {
            return;
        }

        $keepParent = null;
        if (null !== $taxonomy->getParentID()) {
            $parent = $this->taxonomies->byId($taxonomy->getParentID());
            if ($parent instanceof Taxonomy) {
                $keepParent = $parent;
            }
        }

        $this->detachFromCurrentParents($taxonomy, $keepParent?->getId());
        $this->syncParentOnTaxonomy($taxonomy, $keepParent);
        if ($keepParent instanceof Taxonomy) {
            $this->syncChildOnParent($keepParent, $taxonomy);
        }
    }

    private function detachFromCurrentParents(Taxonomy $taxonomy, ?string $keepParentId): void
    {
        $taxonomyId = trim((string) $taxonomy->getId());
        if ('' === $taxonomyId) {
            return;
        }

        foreach ($this->parentsReferencingTaxonomy($taxonomy) as $parent) {
            if (!$parent instanceof Taxonomy) {
                continue;
            }

            if (null !== $keepParentId && (string) $parent->getId() === $keepParentId) {
                continue;
            }

            $this->removeChildFromParent($parent, $taxonomyId);
        }

        $this->removeNonMatchingParentReferences($taxonomy, $keepParentId);
    }

    protected function parentsReferencingTaxonomy(Taxonomy $taxonomy): iterable
    {
        $repository = $this->documentManger->getRepository(Content::class);
        $queryBuilder = $repository->createQueryBuilder();
        $queryBuilder
            ->select()
            ->field('class')
            ->equals(Taxonomy::class)
            ->field('relations.relationId')
            ->equals('__children')
            ->field('relations.references.$id')
            ->equals($taxonomy->getId());

        return $queryBuilder->getQuery()->execute();
    }

    private function removeChildFromParent(Taxonomy $parent, string $taxonomyId): void
    {
        $childrenRelation = $parent->getRelation('__children');
        if (!$childrenRelation instanceof Relation) {
            return;
        }

        $remaining = [];
        foreach ($childrenRelation->getReferences() as $reference) {
            if ($reference instanceof Taxonomy && (string) $reference->getId() === $taxonomyId) {
                continue;
            }

            $remaining[] = $reference;
        }

        $childrenRelation->setReferences($remaining);
    }

    private function removeNonMatchingParentReferences(Taxonomy $taxonomy, ?string $keepParentId): void
    {
        $parentRelation = $taxonomy->getRelation('__parent');
        if (!$parentRelation instanceof Relation) {
            return;
        }

        if (null === $keepParentId) {
            $parentRelation->clearReferences();

            return;
        }

        $remaining = [];
        $alreadyKept = false;
        foreach ($parentRelation->getReferences() as $reference) {
            if (!$reference instanceof Taxonomy || (string) $reference->getId() !== $keepParentId) {
                continue;
            }

            if ($alreadyKept) {
                continue;
            }

            $remaining[] = $reference;
            $alreadyKept = true;
        }

        $parentRelation->setReferences($remaining);
    }

    private function syncParentOnTaxonomy(Taxonomy $taxonomy, ?Taxonomy $parent): void
    {
        if (!$parent instanceof Taxonomy) {
            return;
        }

        $parentRelation = $taxonomy->getRelation('__parent');
        if (!$parentRelation instanceof Relation) {
            $parentRelation = (new Relation())
                ->setRelationId('__parent')
                ->setRelationType('embedded');
            $taxonomy->addRelation($parentRelation);
        }

        $parentRelation->setReferences([$parent]);
    }

    private function syncChildOnParent(Taxonomy $parent, Taxonomy $taxonomy): void
    {
        $childrenRelation = $parent->getRelation('__children');
        if (!$childrenRelation instanceof Relation) {
            $childrenRelation = (new Relation())
                ->setRelationId('__children')
                ->setRelationType('embedded');
            $parent->addRelation($childrenRelation);
        }

        $taxonomyId = trim((string) $taxonomy->getId());
        if ('' === $taxonomyId) {
            $childrenRelation->addReference($taxonomy);

            return;
        }

        $references = [];
        $seen = [];
        foreach ($childrenRelation->getReferences() as $reference) {
            if (!$reference instanceof Taxonomy) {
                continue;
            }

            $id = trim((string) $reference->getId());
            if ('' === $id || isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $references[] = $reference;
        }

        if (!isset($seen[$taxonomyId])) {
            $references[] = $taxonomy;
        }

        $childrenRelation->setReferences($references);
    }
}
