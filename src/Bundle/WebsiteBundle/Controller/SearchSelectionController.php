<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Provider\SolariumProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchSelectionController extends AbstractController
{
    private SolariumProvider $solariumProvider;

    public function __construct(SolariumProvider $solariumProvider)
    {
        $this->solariumProvider = $solariumProvider;
    }

    public function rss(Request $request, SearchSelection $selection): Response
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
