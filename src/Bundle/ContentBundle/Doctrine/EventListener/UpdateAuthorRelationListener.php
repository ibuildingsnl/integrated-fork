<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;

class UpdateAuthorRelationListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        foreach (array_merge($uow->getScheduledDocumentInsertions(), $uow->getScheduledDocumentUpdates()) as $document) {
            if ($document instanceof Article) {
                $authors = [];

                foreach ($document->getAuthors() as $author) {
                    if ($author->getPerson() !== false) {
                        $authors[] = $author->getPerson();
                    }
                }

                if ($relation = $document->getRelation('__authors')) {
                    $document->removeRelation($relation);
                }

                if (\count($authors) > 0) {
                    $relation = new Relation();

                    $relation->setRelationId('__authors');
                    $relation->setRelationType('author');
                    $relation->addReferences($authors);

                    $document->addRelation($relation);
                }

                $class = $dm->getClassMetadata(\get_class($document));
                $uow->recomputeSingleDocumentChangeSet($class, $document);
            }
        }
    }
}
