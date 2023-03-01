<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\NewsletterBundle\Document\CombinedContent;
use Integrated\Bundle\NewsletterBundle\Service\Exception\UnacceptableContentTypeException;

interface CombinatorInterface
{
    /**
     * @param ContentType[] $types
     * @param Channel[] $channels
     *
     * @throws UnacceptableContentTypeException
     */
    public function combine(array $types, array $channels = []): CombinedContent;
}
