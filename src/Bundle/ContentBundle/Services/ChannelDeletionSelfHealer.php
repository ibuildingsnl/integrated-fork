<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;

final class ChannelDeletionSelfHealer
{
    public function heal(Content $content, ChannelDeletionReport $report): void
    {
        if (!$content instanceof Article) {
            return;
        }

        try {
            $content->setAuthors($content->getAuthors());
        } catch (\Throwable $exception) {
            $report->addWarning(new ChannelDeletionWarning(
                'heal',
                $content::class,
                (string) $content->getId(),
                $exception->getMessage(),
                $exception::class
            ));
        }
    }
}
