<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Doctrine\ODM\MongoDB\DocumentManager;
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

    public function manageRelations(Request $request)
    {
        $params = json_decode($request->getContent(), true);
//      "media_id" => "daf99de93f2f3d5e97306bbab4ae5abb"                     REQUIRED, one or many
//      "category_id_target" => "category_2-1"                               REQUIRED, one
//      "category_id_origin" => "3324234"                                    REQUIRED, one

        // Is the user dragging from and to the same folder
        // We are also checking this at the frontend, this is extra
        if ($params['category_id_target'] === $params['category_id_origin']) {
            return new JsonResponse('Origin is same as target');
        }

        $taxonomy = $this->getTaxonomy($params);

        $params = $this->makeSureMediaIDIsArray($params);

        $mediaItems = $this->getMediaItems($params);

        foreach ($mediaItems as $mediaItem) {
            $relations = $this->getOrCreateRelation($mediaItem);

            $relationIDs = $this->getArrayOfRelationIDs($relations);

            $this->removeRelationIfNeeded($relations, $params, $relationIDs);

            $this->addRelationIfNotExists($mediaItem, $relations, $taxonomy, $relationIDs);

            $this->updateQueueToSolr($mediaItem);
        }

        return new JsonResponse('Ok');
    }

    public function getTaxonomy($params)
    {
        // get the Taxonomy (Category) with $params["category_id"]
        if ($params['category_id_target']) {
            return $this->dm->getRepository(Taxonomy::class)->find($params['category_id_target']);
        }
    }

    public function makeSureMediaIDIsArray($params)
    {
        if (true === \is_string($params['media_id'])) {
            $params['media_id'] = [$params['media_id']];
        }

        return $params;
    }

    public function getMediaItems($params)
    {
        return $this->dm->createQueryBuilder(File::class)
            ->field('id')->in($params['media_id'])
            ->getQuery()
            ->execute()->toArray();
    }

    public function getOrCreateRelation($mediaItem)
    {
        if ($relations = $mediaItem->getRelation('mediaitem_channelcategory')) {
            return $relations;
        }
        $relations = (new Relation())
            ->setRelationId('mediaitem_channelcategory')
            ->setRelationType('taxonomy');

        return $relations;
    }

    public function getArrayOfRelationIDs($relations)
    {
        return $relations->getReferences()->map(function ($item) {
            return $item->getID();
        })->toArray();
    }

    public function removeRelationIfNeeded($relations, $params, $relationIDs)
    {
        if ('' !== $params['category_id_origin']) {
            if (\in_array($params['category_id_origin'], $relationIDs)) {
                $removeThisTaxonomy = $this->dm->getRepository(Taxonomy::class)->find($params['category_id_origin']);
                $relations->removeReference($removeThisTaxonomy);
            }
        }
    }

    public function addRelationIfNotExists($mediaItem, $relations, $taxonomy, $relationIDs)
    {
        if (false === \in_array($taxonomy->getID(), $relationIDs)) {
            $messages[] = 'not in array';
            // Add the new taxonomy item
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
