<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Model\TaxonomyRelationModel;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TaxonomyRelationManager.
 *
 * @author Wouter Koppers <wouter@twindigital.com>
 */
class TaxonomyRelationManager
{
    private $dm;
    private QueueSubscriber $queueSubscriber;
    private IndexerInterface $indexer;

    public function __construct(DocumentManager $dm, QueueSubscriber $queueSubscriber, IndexerInterface $indexer)
    {
        $this->dm = $dm;
        $this->queueSubscriber = $queueSubscriber;
        $this->indexer = $indexer;
    }

    public function manageRelationsWithParams(array $params): void
    {
        $request = new Request();

        foreach ($params as $key => $value) {
            $request->attributes->set($key, $value);
        }

        $this->manageRelations($request);
    }

    public function manageRelations(Request $request): JsonResponse
    {
        $taxonomyRelation = new TaxonomyRelationModel($request);

        // Is the user dragging from and to the same folder
        // We are also checking this at the frontend, this is extra
        if (false === $taxonomyRelation->isManagableRelation()) {
            return new JsonResponse('Origin is same as target');
        }

        $taxonomy = $this->getTaxonomy($taxonomyRelation);
        $mediaItems = $this->getMediaItems($taxonomyRelation);

        foreach ($mediaItems as $mediaItem) {
            $relations = $this->getOrCreateRelation($mediaItem);

            $relationIDs = $this->getArrayOfRelationIDs($relations);

            $this->removeRelationIfNeeded($relations, $taxonomyRelation, $relationIDs);

            $this->addRelationIfNotExists($mediaItem, $relations, $taxonomy, $relationIDs);

            $this->updateQueueToSolr($mediaItem);
        }

        return new JsonResponse('Ok');
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
        if ($relations = $mediaItem->getRelation('mediataxonomy')) {
            return $relations;
        }
        $relations = (new Relation())
            ->setRelationId('mediataxonomy')
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

    private function updateQueueToSolr(mixed $content): void
    {
        $queue = $this->queueSubscriber->getQueue();
        $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);
        $this->dm->persist($content);
        $this->dm->flush();
        $this->indexer->setOption('queue.size', 2); // 1 voor het item, 1 voor de commit message
        $this->indexer->execute();
    }
}
