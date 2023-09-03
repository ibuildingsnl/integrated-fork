<?php

namespace Integrated\Bundle\ContentBundle\Twig\Resolver;

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Common\Solr\TitleResolverInterface;

class ChannelTitleResolver implements TitleResolverInterface
{
    public function __construct(
        private readonly ChannelRepository $channels,
    ) {}

    public function getTitle(string $id): string
    {
        try {
            return $this->channels->find($id)?->getName() ?: $id;
        } catch (\Throwable $e) {
            return $id;
        }
    }
}
