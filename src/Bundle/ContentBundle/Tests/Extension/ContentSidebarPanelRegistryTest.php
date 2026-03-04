<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Extension;

use Integrated\Bundle\ContentBundle\Extension\ContentSidebarPanel;
use Integrated\Bundle\ContentBundle\Extension\ContentSidebarPanelProviderInterface;
use Integrated\Bundle\ContentBundle\Extension\ContentSidebarPanelRegistry;
use Integrated\Common\Content\ContentInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ContentSidebarPanelRegistryTest extends TestCase
{
    public function testGetPanelsSortsByPriorityDescending(): void
    {
        $providerA = new class implements ContentSidebarPanelProviderInterface {
            public function getPanels(ContentInterface $content, Request $request): array
            {
                return [
                    new ContentSidebarPanel('seo_health', 'SEO Health', '@App/seo_health.html.twig', [], 5),
                ];
            }
        };

        $providerB = new class implements ContentSidebarPanelProviderInterface {
            public function getPanels(ContentInterface $content, Request $request): array
            {
                return [
                    new ContentSidebarPanel('search_console', 'Search Console', '@App/sc.html.twig', [], 20),
                ];
            }
        };

        $registry = new ContentSidebarPanelRegistry([$providerA, $providerB]);
        $panels = $registry->getPanels($this->createMock(ContentInterface::class), new Request());

        $this->assertCount(2, $panels);
        $this->assertSame('search_console', $panels[0]->getId());
        $this->assertSame('seo_health', $panels[1]->getId());
    }
}
