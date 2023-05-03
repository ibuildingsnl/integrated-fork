<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ThemeBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\ContentBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\FormTypeBundle\Form\Type\FormActionsType;
use Integrated\Bundle\ThemeBundle\Entity\Scraper;
use Integrated\Bundle\ThemeBundle\Form\Type\ScraperType;
use Integrated\Bundle\ThemeBundle\Scraper\Scraper as ScraperService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScraperController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ScraperService $scraper;

    public function __construct(EntityManagerInterface $entityManager, ScraperService $scraper)
    {
        $this->entityManager = $entityManager;
        $this->scraper = $scraper;
    }

    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $result = $this->entityManager->getRepository(Scraper::class)->findBy([], ['channelId' => 'asc', 'name' => 'asc']);

        return $this->render('@IntegratedTheme/scraper/index.html.twig', [
            'result' => $result,
        ]);
    }

    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $scraper = new Scraper();

        $form = $this->createForm(ScraperType::class, $scraper);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($scraper);
            $this->entityManager->flush();

            $this->scraper->prepare($scraper);

            $this->addFlash('success', 'Item created');

            return $this->redirectToRoute('integrated_theme_scraper_edit', ['id' => $scraper->getId()]);
        }

        return $this->render('@IntegratedTheme/scraper/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(Scraper $scraper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ScraperType::class, $scraper);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->scraper->prepare($scraper);

            $this->addFlash('success', 'Item updated');

            return $this->redirectToRoute('integrated_theme_scraper_index');
        }

        return $this->render('@IntegratedTheme/scraper/edit.html.twig', [
            'form' => $form,
        ]);
    }

    public function deleteAction(Scraper $scraper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createDeleteForm($scraper);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($scraper);
            $this->entityManager->flush();

            // Set flash message
            $this->addFlash('success', 'Item updated');

            return $this->redirectToRoute('integrated_theme_scraper_index');
        }

        return $this->render('@IntegratedTheme/scraper/delete.html.twig', [
            'scraper' => $scraper,
            'form' => $form,
        ]);
    }

    protected function createDeleteForm(Scraper $scraper): FormInterface
    {
        $form = $this->createForm(DeleteFormType::class, $scraper, [
            'action' => $this->generateUrl('integrated_theme_scraper_delete', ['id' => $scraper->getId()]),
        ]);

        $form->add('actions', FormActionsType::class, [
            'buttons' => [
                'delete' => ['type' => SubmitType::class, 'options' => ['label' => 'Delete', 'attr' => ['class' => 'btn-danger']]],
            ],
        ]);

        return $form;
    }
}
