<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\NewsletterBundle\Document\CombinedContent;
use Integrated\Bundle\NewsletterBundle\Service\Exception\UnacceptableContentTypeException;

final class DocumentTypeValidator implements CombinatorInterface
{
    private readonly array $acceptedTypes;

    public function __construct(
        private readonly CombinatorInterface $combinator,
        string ...$acceptedTypes,
    ) {
        $this->acceptedTypes = $acceptedTypes;
    }

    public function combine(ContentType ...$types): CombinedContent
    {
        foreach ($types as $type) {
            if (!in_array($type->getClass(), $this->acceptedTypes)) {
                throw UnacceptableContentTypeException::nonWhitelisted($type);
            }
        }
        return $this->combinator->combine(...$types);
    }
}
