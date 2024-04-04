<?php

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\PublishTimeInterface;

class Publication
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    private string $id;

    private string $response = '';

    private string $status = '';

    public function __construct(
        private Content $content,
        private ChannelInterface $channel,
        private PublishTimeInterface $time,
        private array $settings = [],
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

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse($response): void
    {
        $this->response = $response;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSetting(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
    }

    public function __get(string $name)
    {
        return $this->settings[$name] ?? null;
    }
}
