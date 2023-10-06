<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Mock;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Channel\ChannelInterface;

class MemoryPublicationRepository implements PublicationRepositoryInterface
{
    private array $publications = [];

    public function forContent(Content $content): array
    {
        return array_filter($this->publications, fn (Publication $p) => $p->getContent() === $content);
    }

    public function forContentByChannel(Content $content): array
    {
        return array_combine(
            array_map(fn (Publication $p) => $p->getChannel()->getId(), $this->forContent($content)),
            $this->forContent($content),
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
