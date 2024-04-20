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

final class TaxonomyParentRelationListener implements EventSubscriberInterface
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

        if (null === $taxonomy->getParentID()) {
            $parentDocument = $this->documentManger
                ->getRepository(Content::class)
                ->createQueryBuilder()
                ->select()
                ->field('class')
                ->equals(Taxonomy::class)
                ->field('relations.relationId')
                ->equals('__children')
                ->field('relations.$.references.$[].$id')
                ->equals($taxonomy->getParentID())
                ->getQuery()
                ->execute();

            /** @var Content $parent */
            foreach ($parentDocument as $parent) {
                $relation = $parent->getRelation('__children');
                $relation->removeReference($taxonomy);
            }
            return;
        }

        $parent = $this->taxonomies->byId($taxonomy->getParentID());

        if ($parent) {
            $parent->addRelation(
                (new Relation())
                    ->setRelationId('__children')
                    ->setRelationType('embedded')
                    ->addReference($taxonomy)
            );
        }
    }
}
