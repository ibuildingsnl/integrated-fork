<?php

namespace Integrated\Bundle\BrandBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Common\Content\Channel\ChannelInterface;

class Brand
{
    #[Slug(fields: ['name'], separator: '_')]
    private ?string $id = null;

    /** @var Collection<ChannelLink> */
    private Collection $channelLinks;

    public ?BrandProfile $profile;

    public function __construct(?BrandProfile $profile = null)
    {
        $this->profile = $profile;
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

    public function getProfile(): ?BrandProfile
    {
        return $this->profile;
    }

    public function setProfile(?BrandProfile $profile): void
    {
        $this->profile = $profile;
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

    public function hasChannel(ChannelInterface $channel): bool
    {
        foreach ($this->channelLinks as $link) {
            if (!$link->channel) {
                continue;
            }
            if ($link->channel->getId() === $channel->getId()) {
                return true;
            }
        }

        return false;
    }

    public function linkTypeForChannel(ChannelInterface $channel): ?ChannelType
    {
        foreach ($this->channelLinks as $link) {
            if (!$link->channel) {
                continue;
            }
            if ($link->channel->getId() === $channel->getId()) {
                return $link->type;
            }
        }

        return null;
    }

    public function hasAtLeastOneOfChannels(ChannelInterface ...$channels): bool
    {
        foreach ($channels as $channel) {
            foreach ($this->channelLinks as $link) {
                if (!$link->channel) {
                    continue;
                }
                if ($link->channel->getId() === $channel->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @return Collection<ChannelLink> */
    public function getChannelLinks(): Collection
    {
        return $this->channelLinks;
    }

    public function getWebsiteChannel(): ?Channel
    {
        foreach ($this->channelLinks as $link) {
            if ($link->getName() === 'Website') {
                return $link->channel;
            }
        }

        return null;
    }

    public function addChannelLink(ChannelLink $link): void
    {
        if (\in_array($link->getName(), $this->getChannelTypeNames())) {
            throw new \InvalidArgumentException('Duplicate channel type');
        }
        $this->channelLinks[] = $link;
    }

    public function removeChannelLink(ChannelLink $link): void
    {
        foreach ($this->channelLinks as $i => $channelLink) {
            if ($channelLink->getId() === $link->getId()) {
                unset($this->channelLinks[$i]);
            }
        }
    }

    public function hasChannelLink(ChannelLink $candidate): bool
    {
        foreach ($this->channelLinks as $link) {
            if ($link === $candidate) {
                return true;
            }

            $linkId = $link->getId();
            $candidateId = $candidate->getId();

            if ($linkId !== null && $candidateId !== null && $linkId === $candidateId) {
                return true;
            }
        }

        return false;
    }

    public function hasPublished(Content $content): bool
    {
        foreach ($this->channelLinks as $link) {
            if (!$link->channel) {
                continue;
            }
            if ($content->hasChannel($link->channel)) {
                return true;
            }
        }

        return false;
    }
}
