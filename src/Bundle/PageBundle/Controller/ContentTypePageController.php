<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ChannelBundle\Form\Type\ActionsType;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Form\Type\ContentTypePageType;
use Integrated\Bundle\PageBundle\Services\RouteCache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentTypePageController extends AbstractController
{
    private DocumentManager $documentManager;
    private RouteCache $routeCache;

    public function __construct(DocumentManager $documentManager, RouteCache $routeCache)
    {
        $this->documentManager = $documentManager;
        $this->routeCache = $routeCache;
    }

    public function edit(Request $request, ContentTypePage $page): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createEditForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->flush();

            $this->routeCache->clear();

            $this->addFlash('success', 'Page updated');

            return $this->redirectToRoute('integrated_page_page_index');
        }

        return $this->render('@IntegratedPage/content_type_page/edit.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    private function createEditForm(ContentTypePage $page): FormInterface
    {
        $form = $this->createForm(
            ContentTypePageType::class,
            $page,
            [
                'method' => 'PUT',
            ]
        );

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }
}
