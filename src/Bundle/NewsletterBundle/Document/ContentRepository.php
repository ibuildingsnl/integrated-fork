<?php

namespace Integrated\Bundle\NewsletterBundle\Document;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;

interface ContentRepository
{
    public function latestByType(ContentType $type, int $offset = 0): ?Content;

    public function add(Content $content): void;
}
