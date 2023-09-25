<?php

namespace Integrated\Common\Test\Fixture;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;

/**
 * An object mother is a kind of class used in testing to help create example objects that you use for testing.
 *
 * @see https://martinfowler.com/bliki/ObjectMother.html
 */
final class ChannelMother
{
    public static function withId(string $id): Channel
    {
        $c = new Channel();
        $c->setId($id);
        $c->setName($id);
        return $c;
    }

    public static function make(): Channel
    {
        return self::withId((string) rand());
    }
}
