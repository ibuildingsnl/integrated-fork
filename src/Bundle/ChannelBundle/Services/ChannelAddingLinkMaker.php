<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Channel\WebsiteChannel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;

final class ChannelAddingLinkMaker implements LinkMaker
{
    public function __construct(
        private readonly LinkMaker $linkMaker,
    ) {
    }

    public function urlFor(Content $content, WebsiteChannel $preferredChannel): string
    {
        return (
            $content->hasChannel($preferredChannel) // @todo Fix duplicate ChannelInterface
                ? $preferredChannel->getPrimaryDomain()
                : $content->getPrimaryChannel()->getPrimaryDomain()
        ).'/'.$this->linkMaker->urlFor($content, $preferredChannel);
    }
}
