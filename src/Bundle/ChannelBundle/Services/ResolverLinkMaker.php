<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\PageBundle\Services\UrlResolver;
use Integrated\Common\Content\Channel\ChannelInterface;

final class ResolverLinkMaker implements LinkMaker
{
    public function __construct(
        private readonly UrlResolver $urlResolver,
        private readonly BrandRepository $brands,
    ) {
    }

    public function urlFor(Content $content, ?ChannelInterface $preferredChannel): string
    {
        $channel = false;

        if ($preferredChannel === null) {
            $channel = $content->getPrimaryChannel();
        }

        foreach ($this->brands->all() as $brand) {
            if ($brand->hasChannel($preferredChannel)) {
                $channel = $brand->getWebsiteChannel();
            }
        }

        if ($channel instanceof ChannelInterface) {
            return (
                $content->hasChannel($channel)
                    ? $channel->getPrimaryDomain()
                    : $content->getPrimaryChannel()->getPrimaryDomain()
            ).$this->urlResolver->generateUrl($content, $channel->getId());
        } else {
            return '';
        }
    }
}
