<?php

namespace Integrated\Bundle\ContentBundle\Extension;

use Integrated\Common\Content\ContentInterface;
use Symfony\Component\HttpFoundation\Request;

class ContentSidebarPanelRegistry
{
    /** @var iterable<ContentSidebarPanelProviderInterface> */
    private iterable $providers;

    /**
     * @param iterable<ContentSidebarPanelProviderInterface> $providers
     */
    public function __construct(iterable $providers = [])
    {
        $this->providers = $providers;
    }

    /**
     * @return ContentSidebarPanel[]
     */
    public function getPanels(ContentInterface $content, Request $request): array
    {
        $panels = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->getPanels($content, $request) as $panel) {
                if (!$panel instanceof ContentSidebarPanel) {
                    continue;
                }

                $panels[] = $panel;
            }
        }

        usort($panels, static function (ContentSidebarPanel $left, ContentSidebarPanel $right): int {
            return $right->getPriority() <=> $left->getPriority();
        });

        return $panels;
    }
}
