<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\InlineTextBlock;
use Integrated\Bundle\BlockBundle\Form\Type\BlockEditType;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InlineTextBlockController extends AbstractController
{
    private DocumentManager $manager;

    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    public function new(Request $request, AbstractPage $page): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $block = new InlineTextBlock($page);

        $form = $this->createForm(
            BlockEditType::class,
            $block,
            [
                'data_class' => $block::class,
                'type' => $block->getType(),
            ]
        );

        $form->remove('layout');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($block);
            $this->manager->flush();

            return $this->render('@IntegratedBlock/block/saved.iframe.html.twig', ['id' => $block->getId()]);
        }

        return $this->render('@IntegratedBlock/block/new.iframe.html.twig', [
            'form' => $form,
        ]);
    }
}
