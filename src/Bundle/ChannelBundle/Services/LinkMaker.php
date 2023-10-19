<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Content\Channel\ChannelInterface;

interface LinkMaker
{
    public function urlFor(Content $content, ChannelInterface $preferredChannel): string;
}
