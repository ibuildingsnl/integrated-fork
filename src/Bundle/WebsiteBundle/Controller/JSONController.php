<?php

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Block\RelatedContentBlockHandler;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Block\RelatedContentBlock;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Provider\SolariumProvider;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\ThemeBundle\Exception\CircularFallbackException;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

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
     * @throws CircularFallbackException
     * @throws \Exception
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

    /**
     * @throws CircularFallbackException
     */
    public function relatedContentBlock(Request $request): Response
    {
        if (!$blockId = (string) $request->query->get('blockId')) {
            return new Response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$documentId = (string) $request->query->get('documentId')) {
            return new Response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var RelatedContentBlock $block * */
        $block = $this->documentManager->getRepository(RelatedContentBlock::class)->find($blockId);

        $document = $this->documentManager->getRepository(Content::class)->find($documentId);

        if (!$block || !$document) {
            return new Response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $blockHandler = new RelatedContentBlockHandler($this->paginator, $this->requestStack, $this->documentManager);

        $pagination = $blockHandler->getPagination($block, $request, $document);

        return $this->render($this->themeManager->locateTemplate('json/related.'.$request->getRequestFormat('json').'.twig'), [
            'documents' => $pagination->getItems(),
            'totalCount' => $pagination->getTotalItemCount(),
            'maxPages' => ($pagination instanceof SlidingPagination) ? $pagination->getPageCount() : 1,
        ]);
    }
}
