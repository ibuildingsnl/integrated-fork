<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Fixtures;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;

class ObjectWithRelations extends Content
{
    public function __construct()
    {
        parent::__construct();

        $relation = new Relation();

        $relation->setRelationId('dummy');
        $relation->addReference(new Object1());
        $relation->addReference(new Object2());

        $this->addRelation($relation);
    }

    public function __toString()
    {
        return self::class;
    }
}
