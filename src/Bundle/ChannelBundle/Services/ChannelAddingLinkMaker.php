<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;

final class ChannelAddingLinkMaker implements LinkMaker
{
    public function __construct(
        private readonly LinkMaker $linkMaker,
    ) {}

    public function urlFor(Content $content, Channel $preferredChannel): string
    {
        return (
            $content->hasChannel($preferredChannel)
                ? $preferredChannel->getPrimaryDomain()
                : $content->getPrimaryChannel()->getPrimaryDomain()
        ) . '/' . $this->linkMaker->urlFor($content, $preferredChannel);
    }
}
