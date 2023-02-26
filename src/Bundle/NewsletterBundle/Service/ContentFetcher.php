<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\CombinedContent;
use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\Exception\UnacceptableContentTypeException;
use Integrated\Common\ContentType\ResolverInterface;

class ContentFetcher
{
    public function __construct(
        private readonly CombinatorInterface $combinator,
        private readonly ResolverInterface $types,
    ) {
    }

    /** @throws UnacceptableContentTypeException */
    public function fetchFor(Newsletter $newsletter): CombinedContent
    {
        return $this->combinator->combine(array_map(
            fn (string $type) => $this->types->getType($type),
            $newsletter->contentSelection,
        ), $newsletter->getChannels());
    }
}
