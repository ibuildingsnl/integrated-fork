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

use Integrated\Bundle\ChannelBundle\Form\Type\ActionsType;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Form\Type\PageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebsiteController extends AbstractController
{
    public function createPage(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            return new Response('');
        }

        $page = new Page();
        $page->setPath($request->query->get('path'));

        $form = $this->createForm(PageType::class, $page, [
            'action' => $this->generateUrl('integrated_page_page_new', ['returnUrl' => $request->query->get('path')]),
            'short' => true,
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create']]);

        return $this->render('@IntegratedPage/website/create_page.html.twig', [
            'form' => $form,
        ]);
    }
}
