<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\NewsletterBundle\Document\CombinedContent;
use Integrated\Bundle\NewsletterBundle\Document\ContentRepository;

final class ContentCombinator implements CombinatorInterface
{
    public function __construct(
        private readonly ContentRepository $repository,
    ) {
    }

    public function combine(array $types, array $channels = []): CombinedContent
    {
        $offsets = [];
        $combined = new CombinedContent();

        foreach ($types as $type) {
            do {
                $content = $this->repository->mostRecentlyPublished($type, $offsets[$type->getId()] ?? 0, $channels);
                $offsets[$type->getId()] = ($offsets[$type->getId()] ?? 0) + 1;
            } while ($content && $this->exclude($content, $channels));

            if ($content) {
                $combined->addContent($content);
            }
        }

        return $combined;
    }

    public function exclude(Content $content, array $channels): bool
    {
        $a = $content->getCustomFields()->get('ExcludeFromNewsletters');
        if ($a) {
            return true;
        }
        if (empty($channels)) {
            return false;
        }
        foreach ($channels as $channel) {
            if ($content->hasChannel($channel) || empty($content->getChannels())) {
                return false;
            }
        }
        return true;
    }
}
