<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Services;

use Integrated\Bundle\ChannelBundle\Services\ChannelAddingLinkMaker;
use Integrated\Bundle\ChannelBundle\Services\LinkMaker;
use Integrated\Bundle\ChannelBundle\Tests\Mock\NaiveLinkMaker;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use PHPUnit\Framework\TestCase;

final class LinkMakerTest extends TestCase
{
    private array $channels = [];
    private LinkMaker $linkMaker;

    protected function setUp(): void
    {
        $this->channels = [
            'a' => (new Channel())->setId('a')->setName('a'),
            'b' => (new Channel())->setId('b')->setName('b'),
            'c' => (new Channel())->setId('c')->setName('c'),
        ];
        foreach ($this->channels as $tld => $channel) {
            $channel->setPrimaryDomain('channel.'.$tld);
        }
        $this->linkMaker = new ChannelAddingLinkMaker(new NaiveLinkMaker());
    }

    public function testLinkingToAnArticleOnTheSameChannel()
    {
        $article = (new Article())->setId('id')->setContentType('type');
        $article->addChannel($this->channels['a']);

        self::assertSame('channel.a/type/id', $this->linkMaker->urlFor($article, $this->channels['a']));
    }

    public function testLinkingToAnArticleOnOneOfItsChannels()
    {
        $article = (new Article())->setId('id')->setContentType('type');
        $article->addChannel($this->channels['a']);
        $article->addChannel($this->channels['b']);

        self::assertSame('channel.b/type/id', $this->linkMaker->urlFor($article, $this->channels['b']));
    }

    public function testLinkingToAnArticleOnADifferentChannel()
    {
        $article = (new Article())->setId('id')->setContentType('type');
        $article->addChannel($this->channels['a']);
        $article->addChannel($this->channels['b']);

        self::assertSame('channel.a/type/id', $this->linkMaker->urlFor($article, $this->channels['c']));
    }
}
