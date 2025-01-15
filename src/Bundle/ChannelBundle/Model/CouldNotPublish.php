<?php

namespace Integrated\Bundle\ChannelBundle\Model;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Content\Channel\ChannelInterface;

final class CouldNotPublish extends \Exception
{
    public static function encountered(\Throwable $exception, Content $content, ChannelInterface $channel): self
    {
        return new self(\sprintf(
            'Could not publish %s on channel %s: %s',
            $content,
            $channel->getName(),
            $exception->getMessage(),
        ), 0, $exception);
    }
}
