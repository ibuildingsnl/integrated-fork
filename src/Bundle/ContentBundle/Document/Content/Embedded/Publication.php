<?php

namespace Integrated\Bundle\ContentBundle\Document\Content\Embedded;

use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Content\PublishTimeInterface;

class Publication
{
    public function __construct(
        private readonly ChannelInterface $channel,
        private PublishTimeInterface $time,
        private readonly array $settings = [],
    ) {
    }

    public function getChannel(): ChannelInterface
    {
        return $this->channel;
    }

    public function getTime(): PublishTimeInterface
    {
        return $this->time;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
