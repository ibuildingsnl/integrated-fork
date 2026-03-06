<?php

namespace Integrated\Bundle\UserBundle\Tests\Model;

use Integrated\Bundle\ContentBundle\Document\Content\Relation\Relation;
use Integrated\Bundle\UserBundle\Model\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testSetRelationAllowsClearingTheRelation(): void
    {
        $user = new User();
        $relation = new class() extends Relation {
            public function __toString()
            {
                return 'relation';
            }
        };
        $relation->setId('relation-id');
        $relation->setEmail('person@example.com');

        $user->setRelation($relation);
        self::assertSame('person@example.com', $user->getEmail());
        self::assertSame($relation, $user->getRelation());

        $user->setRelation();

        self::assertNull($user->getEmail());
        self::assertNull($user->getRelation());
    }
}
