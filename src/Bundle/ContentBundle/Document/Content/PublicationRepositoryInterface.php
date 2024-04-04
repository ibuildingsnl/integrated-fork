<?php

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Integrated\Common\Content\Channel\ChannelInterface;

interface PublicationRepositoryInterface
{
    /** @return Publication[] */
    public function forContent(Content $content): array;

    /** @return Publication[] */
    public function forContentByChannel(Content $content): array;

    /** @return iterable<Publication> */
    public function forDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): iterable;

    /** @return Publication[] */
    public function forContentOnChannel(Content $content, ChannelInterface $channel): array;

    /** @return iterable<Publication> */
    public function getAvailable(Content $content, ChannelInterface $channel): iterable;

    public function add(Publication $publication): void;

    public function remove(Publication $publication): void;
}
