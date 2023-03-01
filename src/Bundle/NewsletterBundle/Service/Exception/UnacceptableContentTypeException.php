<?php

namespace Integrated\Bundle\NewsletterBundle\Service\Exception;

use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;

class UnacceptableContentTypeException extends \Exception
{
    public static function nonWhitelisted(ContentType $invalidType): self
    {
        return new self("Cannot use content type `{$invalidType->getName()}` in newsletters.");
    }
}
