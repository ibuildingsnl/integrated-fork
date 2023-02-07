<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\NewsletterBundle\Document\CombinedContent;
use Integrated\Bundle\NewsletterBundle\Service\Exception\UnacceptableContentTypeException;

interface CombinatorInterface
{
    /** @throws UnacceptableContentTypeException */
    public function combine(ContentType ...$types): CombinedContent;
}
