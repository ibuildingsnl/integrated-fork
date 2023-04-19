<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\SearchSelection;

use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\UserBundle\Model\GroupInterface;
use Integrated\Bundle\UserBundle\Model\User;

/**
 * Repository for SearchSelection.
 *
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class SearchSelectionRepository extends DocumentRepository
{
    /**
     * @return SearchSelection[]
     *
     * @throws MongoDBException
     */
    public function findForUser(User $user): iterable
    {
        $builder = $this->createQueryBuilder();

        $builder->addOr($builder->expr()->field('userId')->equals($user->getId()));
        $builder->addOr($builder->expr()->field('public')->equals(true));
        $builder->addOr($builder->expr()->field('groupId')->in(
            array_map(fn (GroupInterface $g) => $g->getId(), $user->getGroups())
        ));

        $builder->sort(['public' => 'desc', 'groupId' => 'desc', 'title' => 'asc']);

        return $builder->getQuery()->execute();
    }
}
