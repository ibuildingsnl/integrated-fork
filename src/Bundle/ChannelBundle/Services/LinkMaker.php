<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Channel\WebsiteChannel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;

interface LinkMaker
{
    public function urlFor(Content $content, WebsiteChannel $preferredChannel): string;
}
