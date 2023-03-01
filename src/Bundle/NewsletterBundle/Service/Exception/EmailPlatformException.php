<?php

namespace Integrated\Bundle\NewsletterBundle\Service\Exception;

final class EmailPlatformException extends \Exception
{
    public static function from(\Throwable $previous): self
    {
        return new self($previous->getMessage(), $previous->getCode(), $previous);
    }
}
