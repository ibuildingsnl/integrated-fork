<?php

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Provider\SolariumProvider;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RSSSearchSelectionController extends AbstractController
{
    public function __construct(
        private readonly SolariumProvider $solariumProvider,
        private readonly ThemeManager $themeManager
    ) {
    }

    public function rss(Request $request, SearchSelection $selection): Response
    {
        $block = new ContentBlock();
        $block->setSearchSelection($selection);

        if ($itemsPerPage = $request->query->get('itemsPerPage')) {
            $itemsPerPage = ($itemsPerPage > 500) ? 500 : $itemsPerPage;
            $block->setItemsPerPage($itemsPerPage);
        }

        return $this->render($this->themeManager->locateTemplate('rss/rss.'.$request->getRequestFormat('xml').'.twig'), [
            'selection' => $selection,
            'documents' => $this->solariumProvider->execute($block, $request),
        ]);
    }
}
