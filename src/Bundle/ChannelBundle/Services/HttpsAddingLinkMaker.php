<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\ChannelInterface;

final class HttpsAddingLinkMaker implements LinkMaker
{
    public function __construct(
        private readonly LinkMaker $linkMaker,
    ) {
    }

    public function urlFor(Content $content, ChannelInterface $preferredChannel): string
    {
        return 'https://' . $this->linkMaker->urlFor($content, $preferredChannel);
    }
}
