<?php

namespace Integrated\Bundle\NewsletterBundle\Document;

use Integrated\Bundle\ContentBundle\Document\Content\Content;

class CombinedContent extends Content
{
    private array $content = [];

    public function addContent(Content $content)
    {
        $this->content[] = $content;
    }

    /** @return Content[] */
    public function getContent(): array
    {
        return $this->content;
    }

    public function __toString()
    {
        return implode($this->content);
    }
}
