<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\PageBundle\Services\UrlResolver;

final class ResolverLinkMaker implements LinkMaker
{
    public function __construct(
        private readonly UrlResolver $urlResolver,
    ) {}

    public function urlFor(Content $content, Channel $preferredChannel): string
    {
        return $this->urlResolver->generateUrl($content, $preferredChannel->getId());
    }
}
