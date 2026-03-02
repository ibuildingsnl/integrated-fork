<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\UserBundle\Model\UserInterface;

interface ArticleSearchServiceInterface
{
    /**
     * @return array<int, array{key: string, label: string}>
     */
    public function getAvailableChannels(?UserInterface $user): array;

    /**
     * @return array<int, array{key: string, label: string}>
     */
    public function getAvailableContentTypes(): array;

    public function findChannel(string $channelId): ?Channel;

    /**
     * @param array<int, string> $contentTypeIds
     *
     * @return array<int, array{id: string, title: string, subtitle: string, text: string, url: string}>
     */
    public function searchInChannel(Channel $channel, string $term, array $contentTypeIds): array;
}
