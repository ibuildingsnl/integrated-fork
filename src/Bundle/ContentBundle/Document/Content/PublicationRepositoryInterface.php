<?php

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Integrated\Common\Content\Channel\ChannelInterface;

interface PublicationRepositoryInterface
{
    /** @return Publication[] */
    public function forContent(Content $content): array;

    /** @return Publication[] */
    public function forContentByChannel(Content $content): array;

    /** @return Publication[] */
    public function forDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array;

    /** @return Publication[] */
    public function forContentOnChannel(Content $content, ChannelInterface $channel): array;

    /** @return Publication[] */
    public function availablePublicationsForChannelByContent(Content $content, ChannelInterface $channel): array;

    public function add(Publication $publication): void;

    public function remove(Publication $publication): void;
}
