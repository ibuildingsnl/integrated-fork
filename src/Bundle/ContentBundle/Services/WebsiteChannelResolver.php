<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\Channel\ChannelManagerInterface;

final class WebsiteChannelResolver
{
    /** @var list<ChannelInterface>|null */
    private ?array $websiteChannels = null;

    public function __construct(
        private readonly ChannelManagerInterface $channelManager,
    ) {
    }

    /**
     * @return list<ChannelInterface>
     */
    public function getWebsiteChannels(): array
    {
        if (\is_array($this->websiteChannels)) {
            return $this->websiteChannels;
        }

        $channels = $this->channelManager->findBy([
            '$or' => [
                ['type.$id' => 'website'],
                ['type.name' => 'Website'],
            ],
        ]);
        usort(
            $channels,
            static fn (ChannelInterface $left, ChannelInterface $right): int => strcasecmp((string) $left->getName(), (string) $right->getName())
                ?: strcmp((string) $left->getId(), (string) $right->getId())
        );

        $this->websiteChannels = $channels;

        return $this->websiteChannels;
    }

    /**
     * @return list<string>
     */
    public function getWebsiteChannelIds(): array
    {
        return array_keys($this->getWebsiteChannelChoices());
    }

    /**
     * @return array<string, string>
     */
    public function getWebsiteChannelChoices(): array
    {
        $choices = [];

        foreach ($this->getWebsiteChannels() as $channel) {
            $channelId = trim((string) ($channel->getId() ?? ''));
            if ($channelId === '') {
                continue;
            }

            $channelName = trim((string) ($channel->getName() ?? ''));
            $choices[$channelId] = $channelName !== '' ? $channelName : $channelId;
        }

        return $choices;
    }
}
