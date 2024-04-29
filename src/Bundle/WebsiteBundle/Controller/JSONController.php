<?php

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Provider\SolariumProvider;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\ThemeBundle\Exception\CircularFallbackException;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JSONController extends AbstractController
{
    public function __construct(
        private readonly SolariumProvider $solariumProvider,
        private readonly ThemeManager $themeManager
    ) {
    }

    /**
     * @throws CircularFallbackException
     */
    public function searchSelectionJson(Request $request, SearchSelection $searchSelection): Response
    {
        $block = new ContentBlock();
        $block->setSearchSelection($searchSelection);

        if ($itemsPerPage = $request->query->get('limit')) {
            $itemsPerPage = ($itemsPerPage > 500) ? 500 : $itemsPerPage;
            $block->setItemsPerPage($itemsPerPage);
        }

        $documents = $this->solariumProvider->execute($block, $request);

        $maxPages = round($documents->getTotalItemCount() / $itemsPerPage);

        return $this->render($this->themeManager->locateTemplate('json/index.'.$request->getRequestFormat('json').'.twig'), [
            'documents' => $documents,
            'totalCount' => $documents->getTotalItemCount(),
            'maxPages' => $maxPages,
        ]);
    }
}
