<?php

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Provider\SolariumProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RSSSearchSelectionController extends AbstractController
{
    public function __construct(
        private readonly SolariumProvider $solariumProvider
    ) {
    }

    /**
     * @Template
     *
     * @return array
     */
    public function rss(Request $request, SearchSelection $selection)
    {
        $block = new ContentBlock();
        $block->setSearchSelection($selection);

        if ($itemsPerPage = $request->query->get('itemsPerPage')) {
            $itemsPerPage = ($itemsPerPage > 500) ? 500 : $itemsPerPage;
            $block->setItemsPerPage($itemsPerPage);
        }

        return $this->render('@IntegratedWebsite/search_selection/rss.'.$request->getRequestFormat('xml').'.twig', [
            'selection' => $selection,
            'documents' => $this->solariumProvider->execute($block, $request),
        ]);
    }
}
