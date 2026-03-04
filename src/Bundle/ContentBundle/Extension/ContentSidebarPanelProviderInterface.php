<?php

namespace Integrated\Bundle\ContentBundle\Extension;

use Integrated\Common\Content\ContentInterface;
use Symfony\Component\HttpFoundation\Request;

interface ContentSidebarPanelProviderInterface
{
    /**
     * @return ContentSidebarPanel[]
     */
    public function getPanels(ContentInterface $content, Request $request): array;
}
