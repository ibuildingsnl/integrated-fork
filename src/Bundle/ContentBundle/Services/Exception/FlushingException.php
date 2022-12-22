<?php

namespace Integrated\Bundle\ContentBundle\Services\Exception;

class FlushingException extends \Exception
{
    public static function from(\Throwable $previous): self
    {
        return new self(
            'Could not flush the content: '.$previous->getMessage(),
            $previous,
            $previous->getCode(),
        );
    }
}
