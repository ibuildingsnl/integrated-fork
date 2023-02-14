<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\NewsletterBundle\Document\CombinedContent;
use Integrated\Bundle\NewsletterBundle\Document\ContentRepository;

final class ContentCombinator implements CombinatorInterface
{
    public function __construct(
        private readonly ContentRepository $repository,
    ) {
    }

    public function combine(ContentType ...$types): CombinedContent
    {
        $offsets = [];
        $combined = new CombinedContent();
        foreach ($types as $type) {
            do {
                $content = $this->repository->mostRecentlyPublished($type, $offsets[$type->getId()] ?? 0);
                $offsets[$type->getId()] = ($offsets[$type->getId()] ?? 0) + 1;
            } while ($content && $content->getCustomFields()->get('ExcludeFromNewsletters'));
            if (!$content) {
                continue;
            }
            $combined->addContent($content);
        }

        return $combined;
    }
}
