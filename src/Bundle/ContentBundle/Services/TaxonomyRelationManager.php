<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Model\TaxonomyRelationModel;
use Integrated\Common\Services\MainFlusher;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TaxonomyRelationManager.
 *
 * @author Wouter Koppers <wouter@twindigital.com>
 */
class TaxonomyRelationManager
{
    private $dm;
    private MainFlusher $flusher;

    public function __construct(DocumentManager $dm, MainFlusher $flusher)
    {
        $this->dm = $dm;
        $this->flusher = $flusher;
    }

    public function manageRelationsWithParams(array $params): void
    {
        $request = new Request();

        foreach ($params as $key => $value) {
            $request->attributes->set($key, $value);
        }

        $this->manageRelations($request);
    }

    public function manageRelations(Request $request): void
    {
        $taxonomyRelation = new TaxonomyRelationModel($request);

        // Is the user dragging from and to the same folder
        // We are also checking this at the frontend, this is extra
        if (false === $taxonomyRelation->isManagableRelation()) {
            return;
        }

        $mediaItems = $this->getMediaItems($taxonomyRelation);

        foreach ($mediaItems as $mediaItem) {
            $taxonomy = $this->getTaxonomy($taxonomyRelation);

            $relations = $this->getOrCreateRelation($mediaItem);

            $relationIDs = $this->getArrayOfRelationIDs($relations);

            $this->removeRelationIfNeeded($relations, $taxonomyRelation, $relationIDs);

            $this->addRelationIfNotExists($mediaItem, $relations, $taxonomy, $relationIDs);
        }

        $this->runSolrQueue();
    }

    public function runSolrQueue(): void
    {
        $this->flusher->flush();
    }

    private function getTaxonomy(TaxonomyRelationModel $taxonomyRelation): Taxonomy
    {
        return $this->dm->getRepository(Taxonomy::class)->find($taxonomyRelation->getCategoryIdTarget());
    }

    public function getMediaItems(TaxonomyRelationModel $taxonomyRelation): array
    {
        return $this->dm->createQueryBuilder(File::class)
            ->field('id')->in($taxonomyRelation->getMediaId())
            ->getQuery()
            ->execute()->toArray();
    }

    private function getOrCreateRelation(mixed $mediaItem): Relation
    {
        if ($relations = $mediaItem->getRelation('media_taxonomy')) {
            return $relations;
        }
        $relations = (new Relation())
            ->setRelationId('media_taxonomy')
            ->setRelationType('taxonomy');

        return $relations;
    }

    private function getArrayOfRelationIDs(Relation $relations): array
    {
        return $relations->getReferences()->map(function ($item) {
            return $item->getID();
        })->toArray();
    }

    private function removeRelationIfNeeded(Relation $relations, TaxonomyRelationModel $taxonomyRelation, array $relationIDs): void
    {
        if ('' !== $taxonomyRelation->getCategoryIdOrigin()) {
            if (\in_array($taxonomyRelation->getCategoryIdOrigin(), $relationIDs)) {
                $removeThisTaxonomy = $this->dm->getRepository(Taxonomy::class)->find($taxonomyRelation->getCategoryIdOrigin());
                $relations->removeReference($removeThisTaxonomy);
            }
        }
    }

    private function addRelationIfNotExists(mixed $mediaItem, Relation $relations, Taxonomy $taxonomy, array $relationIDs): void
    {
        if (false === \in_array($taxonomy->getID(), $relationIDs)) {
            $relations->addReference($taxonomy);
            $mediaItem->addRelation($relations);
        }
    }
}
