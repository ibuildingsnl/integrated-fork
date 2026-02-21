<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Bulk\BulkAction;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionConfirmType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkConfigureType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkSelectionType;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Common\Bulk\BulkHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BulkController extends AbstractController
{
    private DocumentManager $manager;
    private ContentProvider $contentProvider;
    private BulkHandlerInterface $bulkHandler;

    public function __construct(
        DocumentManager $manager,
        ContentProvider $contentProvider,
        BulkHandlerInterface $bulkHandler,
    ) {
        $this->manager = $manager;
        $this->contentProvider = $contentProvider;
        $this->bulkHandler = $bulkHandler;
    }

    public function select(Request $request, ?BulkAction $bulk = null): Response
    {
        // Fetch Content selection.
        $limit = 1000;

        if ($bulk) {
            $request->query->replace($bulk->getFilters());
        }

        if (!$content = $this->contentProvider->getContentFromSolr($request, $limit + 1)) {
            return $this->redirectToRoute('integrated_content_content_index', $request->query->all());
        }

        $form = $this->createForm(BulkSelectionType::class, $bulk, ['content' => \array_slice($content, 0, $limit)]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var BulkAction $bulk */
            $bulk = $form->getData();
            $bulk->setFilters($request->query->all());

            if (!$bulk->getId()) {
                $this->manager->persist($bulk);
            }

            $this->manager->flush();

            return $this->redirectToRoute('integrated_content_bulk_configure', ['id' => $bulk->getId()]);
        }

        return $this->render('@IntegratedContent/bulk/select.html.twig', [
            'content' => $content,
            'limit' => $limit,
            'form' => $form,
        ]);
    }

    public function configure(Request $request, BulkAction $bulk): Response
    {
        if ($bulk->getExecutedAt()) {
            return $this->redirectToRoute('integrated_content_content_index', $bulk->getFilters());
        }

        $form = $this->createForm(BulkConfigureType::class, $bulk, ['content' => $bulk->getSelection()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            return $this->redirectToRoute('integrated_content_bulk_confirm', ['id' => $bulk->getId()]);
        }

        return $this->render('@IntegratedContent/bulk/configure.html.twig', [
            'id' => $bulk->getId(),
            'selection' => \count($bulk->getSelection()),
            'form' => $form,
        ]);
    }

    public function confirm(Request $request, BulkAction $bulk): Response
    {
        $this->preventTimeout();

        if ($bulk->getExecutedAt()) {
            return $this->redirectToRoute('integrated_content_content_index', $bulk->getFilters());
        }

        $form = $this->createForm(BulkActionConfirmType::class, $bulk, ['content' => $bulk->getSelection()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bulkHandler->execute($bulk->getSelection(), $bulk->getActions());
                $bulk->setExecutedAt(new \DateTime());

                $this->manager->flush();

                $this->addFlash('success', 'All bulk actions were executed successfully. Indexing operations will be executed in the background');

                return $this->redirectToRoute('integrated_content_content_index', $bulk->getFilters());
            } catch (\Exception $e) {
                $this->addFlash(
                    'danger',
                    'Whoops! It seems something went wrong during the execution of this bulk action! The following error has given: "'.$e->getMessage().'"'
                );
            }
        }

        return $this->render('@IntegratedContent/bulk/confirm.html.twig', [
            'id' => $bulk->getId(),
            'selection' => \count($bulk->getSelection()),
            'form' => $form,
        ]);
    }

    /**
     * Try to prevent reaching the timeout on large bulk actions.
     */
    private function preventTimeout(): void
    {
        ini_set('max_execution_time', '600');
    }
}
