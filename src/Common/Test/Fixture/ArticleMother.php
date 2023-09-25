<?php

namespace Integrated\Common\Test\Fixture;

use Integrated\Bundle\ContentBundle\Document\Content\Article;

/**
 * An object mother is a kind of class used in testing to help create example objects that you use for testing.
 *
 * @see https://martinfowler.com/bliki/ObjectMother.html
 */
final class ArticleMother
{
    public static function withoutChannels(): Article
    {
        return new Article();
    }

    public static function withChannel(string $id = null): Article
    {
        $a = self::withoutChannels();
        $a->addChannel($id ? ChannelMother::withId($id) : ChannelMother::make());
        return $a;
    }
}
