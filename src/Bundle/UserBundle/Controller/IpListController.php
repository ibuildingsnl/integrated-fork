<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Controller;

use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\UserBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\UserBundle\Form\Type\IpListFormType;
use Integrated\Bundle\UserBundle\Model\IpList;
use Integrated\Bundle\UserBundle\Model\IpListManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IpListController extends AbstractController
{
    private IpListManagerInterface $manager;
    private PaginatorInterface $paginator;

    public function __construct(IpListManagerInterface $manager, PaginatorInterface $paginator)
    {
        $this->manager = $manager;
        $this->paginator = $paginator;
    }

    public function index(Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $paginator = $this->paginator->paginate(
            $this->manager->findAll(),
            $request->query->get('page', 1),
            15
        );

        return $this->render('@IntegratedUser/ip_list/index.html.twig', [
            'lists' => $paginator,
        ]);
    }

    public function new(Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createNewForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_iplist_index');
            }

            if ($form->isValid()) {
                $list = $form->getData();

                $this->manager->persist($list);

                $this->addFlash('success', \sprintf(
                    'Added the ip %s to the whitelist',
                    $list->getIp()->getProtocolAppropriateAddress()
                ));

                return $this->redirectToRoute('integrated_user_iplist_index');
            }
        }

        return $this->render('@IntegratedUser/ip_list/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(IpList $list, Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createEditForm($list);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_iplist_index');
            }

            if ($form->isValid()) {
                $this->manager->persist($list);

                $this->addFlash('success', \sprintf(
                    'The changes to the ip %s are saved',
                    $list->getIp()->getProtocolAppropriateAddress()
                ));

                return $this->redirectToRoute('integrated_user_iplist_index');
            }
        }

        return $this->render('@IntegratedUser/ip_list/edit.html.twig', [
            'list' => $list,
            'form' => $form,
        ]);
    }

    public function delete(IpList $list, Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createDeleteForm($list);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_iplist_index');
            }

            if ($form->isValid()) {
                $this->manager->remove($list);

                $this->addFlash('success', \sprintf(
                    'The ip %s is removed from the whitelist',
                    $list->getIp()->getProtocolAppropriateAddress()
                ));

                return $this->redirectToRoute('integrated_user_iplist_index');
            }
        }

        return $this->render('@IntegratedUser/ip_list/delete.html.twig', [
            'list' => $list,
            'form' => $form,
        ]);
    }

    private function createNewForm(): Form
    {
        $form = $this->createForm(IpListFormType::class, null, [
            'action' => $this->generateUrl('integrated_user_iplist_new'),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    private function createEditForm(IpList $list): Form
    {
        $form = $this->createForm(IpListFormType::class, $list, [
            'action' => $this->generateUrl('integrated_user_iplist_edit', ['id' => $list->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    private function createDeleteForm(IpList $list): Form
    {
        $form = $this->createForm(DeleteFormType::class, $list, [
            'action' => $this->generateUrl('integrated_user_iplist_delete', ['id' => $list->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form;
    }
}
