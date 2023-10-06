<?php

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Content\PublishTimeInterface;

class Publication
{
    private string $id;

    public function __construct(
        private readonly Content $content,
        private readonly ChannelInterface $channel,
        private PublishTimeInterface $time,
        private readonly array $settings = [],
    ) {
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): Content
    {
        return $this->content;
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

    public function __get(string $name)
    {
        return $this->settings[$name] ?? null;
    }
}
