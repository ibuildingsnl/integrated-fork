<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Mock;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Channel\ChannelInterface;

class MemoryPublicationRepository implements PublicationRepositoryInterface
{
    private array $publications = [];

    public function forContentByChannel(Content $content): array
    {
        $publications = array_filter($this->publications, fn (Publication $p) => $p->getContent() === $content);
        return array_combine(
            array_map(fn(Publication $p) => $p->getChannel()->getId(), $publications),
            $publications,
        );
    }

    public function forContentOnChannel(Content $content, ChannelInterface $channel): array
    {
        return array_filter(
            $this->publications,
            fn (Publication $p) => $p->getContent() === $content && $p->getChannel() === $channel,
        );
    }

    public function add(Publication $publication): void
    {
        $this->publications[] = $publication;
    }

    public function remove(Publication $publication): void
    {
        $this->publications = array_filter($this->publications, fn (Publication $p) => $p !== $publication);
    }
}
