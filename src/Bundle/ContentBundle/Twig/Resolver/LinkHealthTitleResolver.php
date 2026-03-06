<?php

namespace Integrated\Bundle\ContentBundle\Twig\Resolver;

use Integrated\Common\Solr\TitleResolverInterface;

class LinkHealthTitleResolver implements TitleResolverInterface
{
    public function getTitle(string $id): string
    {
        return match (strtolower($id)) {
            'ok' => 'Healthy',
            'warning' => 'Warning',
            'broken' => 'Broken',
            'unknown' => 'Unknown',
            default => $id,
        };
    }
}
