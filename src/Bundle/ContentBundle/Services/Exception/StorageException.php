<?php

namespace Integrated\Bundle\ContentBundle\Services\Exception;

class StorageException extends \Exception
{
    public static function from(\Throwable $previous): self
    {
        return new self(
            'Could not save the content: ' . $previous->getMessage(),
            $previous,
            $previous->getCode(),
        );
    }
}
