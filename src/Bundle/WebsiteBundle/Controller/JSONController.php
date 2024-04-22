<?php

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Provider\SolariumProvider;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class JSONController extends AbstractController
{
    public function __construct(
        private readonly SolariumProvider $solariumProvider,
        private readonly ThemeManager $themeManager
    ) {
    }

    /**
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rss(Request $request, SearchSelection $searchSelection)
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
