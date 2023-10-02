<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Mock;

use Integrated\Bundle\ChannelBundle\Services\LinkMaker;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;

final class NaiveLinkMaker implements LinkMaker
{
    public function urlFor(Content $content, Channel $preferredChannel): string
    {
        return $content->getContentType() . '/' . $content->getId();
    }
}
