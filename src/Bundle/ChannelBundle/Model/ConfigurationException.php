<?php

namespace Integrated\Bundle\ChannelBundle\Model;

final class ConfigurationException extends \Exception
{
    public static function encountered(\Throwable $e): self
    {
        return new self('There is a problem with the connector configuration: '.$e->getMessage(), 0, $e);
    }
}
