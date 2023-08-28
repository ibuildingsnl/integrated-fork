<?php

namespace Integrated\Bundle\BrandBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Common\Content\Channel\ChannelInterface;

class Brand
{
    #[Slug(fields: ['name'], separator: '_')]
    private ?string $id = null;
    /** @var Collection|ChannelLink[] */
    private iterable $channelLinks;

    public function __construct(
        public ?BrandProfile $profile = null,
    ) {
        $this->channelLinks = new ArrayCollection();
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->profile?->name ?: '';
    }

    /** @return string[] */
    public function getChannelTypeNames(): array
    {
        $types = [];
        foreach ($this->channelLinks as $link) {
            $types[] = $link->type->name;
        }
        return $types;
    }

    /** @return ChannelInterface[] */
    public function getChannels(?string $type = null): array
    {
        $channels = [];
        foreach ($this->channelLinks as $link) {
            if (!$type || $link->type->tag === $type) {
                $channels[] = $link->channel;
            }
        }
        return $channels;
    }

    public function addChannel(ChannelInterface $channel, LinkType $type, bool $default = true): void
    {
        if (in_array($type->name, $this->getChannelTypeNames())) {
            throw new \InvalidArgumentException('Duplicate channel type');
        }
        $this->channelLinks[] = new ChannelLink($type, $channel, $default);
    }

    /** @return ChannelLink[] */
    public function getChannelLinks(): iterable
    {
        return $this->channelLinks;
    }
}
