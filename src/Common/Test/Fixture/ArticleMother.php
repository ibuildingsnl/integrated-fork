<?php

namespace Integrated\Common\Test\Fixture;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;

/**
 * An object mother is a kind of class used in testing to help create example objects that you use for testing.
 *
 * @see https://martinfowler.com/bliki/ObjectMother.html
 */
final class ArticleMother
{
    public function __construct(
        private PublicationRepositoryInterface $publications,
    ) {
    }

    public function withoutChannels(): Article
    {
        $a = new Article();
        $a->setTitle('title');
        $a->setContent('content');
        $a->setId(random_bytes(32));

        return $a;
    }

    public function withChannel(?string $id = null): Article
    {
        $a = $this->withoutChannels();
        $a->addChannel($id ? ChannelMother::withId($id) : ChannelMother::make());

        return $a;
    }

    public function withPublication(
        PublishTime|\DateTimeInterface $publishTime,
        ?string $channelId = null,
        array $publicationSettings = [],
    ): Article {
        if ($publishTime instanceof \DateTimeInterface) {
            $publishTime = (new PublishTime())->setStartDate($publishTime);
        }
        $c = $channelId ? ChannelMother::withId($channelId) : ChannelMother::make();
        $a = $this->withoutChannels();
        $a->setPublishTime($publishTime);
        $a->addChannel($c);
        $this->publications->add(new Publication($a, $c, $publishTime, $publicationSettings));

        return $a;
    }

    public function withPublications(
        PublishTime|\DateTimeInterface ...$publishTimes,
    ): Article {
        $a = $this->withoutChannels();
        $min = null;
        $max = null;
        foreach ($publishTimes as $publishTime) {
            if ($publishTime instanceof \DateTimeInterface) {
                $publishTime = (new PublishTime())->setStartDate($publishTime);
            }
            $c = ChannelMother::make();
            $a->addChannel($c);
            $this->publications->add(new Publication($a, $c, $publishTime, []));
            if ($publishTime->getStartDate() && (!$min || $publishTime->getStartDate() < $min)) {
                $min = $publishTime->getStartDate();
            }
            if ($publishTime->getEndDate() && (!$max || $publishTime->getEndDate() > $max)) {
                $max = $publishTime->getEndDate();
            }
        }
        $p = new PublishTime();
        if ($min) {
            $p->setStartDate($min);
        }
        if ($max) {
            $p->setEndDate($max);
        }
        $a->setPublishTime($p);

        return $a;
    }

    public function disabled(): Article
    {
        $a = $this->withChannel();
        $a->setDisabled(true);

        return $a;
    }
}
