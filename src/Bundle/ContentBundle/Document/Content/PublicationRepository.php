<?php

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Integrated\Common\Channel\ChannelInterface;

interface PublicationRepository
{
    public function find(string $id): ?Publication;

    /** @return Publication[] */
    public function publishedBetween(\DateTime $start, \DateTime $end): array;

    /** @return Publication[] */
    public function forContent(Content|string $content): array;

    /** @return Publication[] */
    public function forChannel(ChannelInterface|string $channel): array;

    public function add(Publication $publication): void;
}
