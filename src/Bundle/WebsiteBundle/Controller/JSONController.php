<?php

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\ContentBundle\Block\RelatedContentBlockHandler;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Block\RelatedContentBlock;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Provider\SolariumProvider;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class JSONController extends AbstractController
{
    public function __construct(
        private readonly SolariumProvider $solariumProvider,
        private readonly PaginatorInterface $paginator,
        private readonly RequestStack $requestStack,
        private readonly DocumentManager $documentManager,
        private readonly ThemeManager $themeManager
    ) {
    }

    /**
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchSelection(Request $request, SearchSelection $searchSelection)
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

    /**
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function relatedContentBlock(Request $request)
    {
        $blockId = $request->query->get('blockId');
        $documentId = $request->query->get('documentId');

        if (!$blockId || !$documentId) {
            return;
        }

        /** @var RelatedContentBlock $block */
        $block = $this->documentManager
            ->getRepository(Block::class)
            ->findOneBy(['_id' => $blockId]);

        $document = $this->documentManager
            ->getRepository(Content::class)
            ->findOneBy(['_id' => $documentId]);

        if (!$block instanceof RelatedContentBlock || !$document instanceof Content) {
            return;
        }

        $blockHandler = new RelatedContentBlockHandler($this->paginator, $this->requestStack, $this->documentManager);

        $pagination = $blockHandler->getPagination($block, $request, $document);

        return $this->render($this->themeManager->locateTemplate('json/related.'.$request->getRequestFormat('json').'.twig'), [
            'documents' => $pagination->getItems(),
            'totalCount' => $pagination->getTotalItemCount(),
            'maxPages' => $pagination->getPageCount(),
        ]);
    }
}
