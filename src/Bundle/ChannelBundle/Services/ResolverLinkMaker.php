<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\PageBundle\Services\UrlResolver;
use Integrated\Common\Channel\ChannelInterface;

final class ResolverLinkMaker implements LinkMaker
{
    public function __construct(
        private readonly UrlResolver $urlResolver,
    ) {}

    public function urlFor(Content $content, ChannelInterface $preferredChannel): string
    {
        return $this->urlResolver->generateUrl($content, $preferredChannel->getId());
    }
}
