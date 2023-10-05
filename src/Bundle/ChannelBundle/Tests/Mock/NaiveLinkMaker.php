<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Mock;

use Integrated\Bundle\ChannelBundle\Services\LinkMaker;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\ChannelInterface;

final class NaiveLinkMaker implements LinkMaker
{
    public function urlFor(Content $content, ChannelInterface $preferredChannel): string
    {
        return $content->getContentType() . '/' . $content->getId();
    }
}
