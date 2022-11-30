<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Model\TaxonomyRelationModel;
use Symfony\Component\HttpFoundation\Request;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    public function manageRelationsWithParams($params) {
        $request = new Request;

        foreach ($params as $key => $value) {
            $request->attributes->set($key, $value);
        }

        $this->manageRelations($request);
    }

    public function findParams(Request $request): array {
        if (null !== json_decode($request->getContent(), true)) {
            return json_decode($request->getContent(), true);
        }

        if (null !== $request->get('category_id_target')) {
            return [
                'category_id_origin' => '',
                'category_id_target' => $request->get('category_id_target'),
                'media_id' => $request->get('media_id'),
            ];
        }

    }

    public function manageRelations(Request $request)
    {
        $taxonomyRelation = new TaxonomyRelationModel($request);

        //fix this
        $params = $this->findParams($request);

        // Is the user dragging from and to the same folder
        // We are also checking this at the frontend, this is extra
        if (true === $taxonomyRelation->isTargetSameAsOrigin()) {
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

    public function getTaxonomy($taxonomyRelation)
    {
        if ($taxonomyRelation->getCategoryIdTarget()) {
            return $this->dm->getRepository(Taxonomy::class)->find($taxonomyRelation->getCategoryIdTarget());
        }
    }

    public function makeSureMediaIDIsArray($params)
    {
        if (true === \is_string($params['media_id'])) {
            $params['media_id'] = [$params['media_id']];
        }

        return $params;
    }

    public function getMediaItems($taxonomyRelation)
    {
        return $this->dm->createQueryBuilder(File::class)
            ->field('id')->in($taxonomyRelation->getMediaId())
            ->getQuery()
            ->execute()->toArray();
    }

    public function getOrCreateRelation($mediaItem)
    {
        if ($relations = $mediaItem->getRelation('mediataxonomy')) {
            return $relations;
        }
        $relations = (new Relation())
            ->setRelationId('mediataxonomy')
            ->setRelationType('taxonomy');

        return $relations;
    }

    public function getArrayOfRelationIDs($relations)
    {
        return $relations->getReferences()->map(function ($item) {
            return $item->getID();
        })->toArray();
    }

    public function removeRelationIfNeeded($relations, $taxonomyRelation, $relationIDs)
    {
        if ('' !== $taxonomyRelation->getCategoryIdOrigin()) {
            if (\in_array($taxonomyRelation->getCategoryIdOrigin(), $relationIDs)) {
                $removeThisTaxonomy = $this->dm->getRepository(Taxonomy::class)->find($taxonomyRelation->getCategoryIdOrigin());
                $relations->removeReference($removeThisTaxonomy);
            }
        }
    }

    public function addRelationIfNotExists($mediaItem, $relations, $taxonomy, $relationIDs)
    {
        if (false === \in_array($taxonomy->getID(), $relationIDs)) {
            $messages[] = 'not in array';
            $relations->addReference($taxonomy);
            $mediaItem->addRelation($relations);
        }
    }

    public function updateQueueToSolr($content)
    {
        $queue = $this->queueSubscriber->getQueue();
        $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);
        $this->dm->persist($content);
        $this->dm->flush();
        $this->indexer->setOption('queue.size', 2); // 1 voor het item, 1 voor de commit message
        $this->indexer->execute();
    }
}
