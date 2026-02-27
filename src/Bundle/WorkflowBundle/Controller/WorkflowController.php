<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Integrated\Bundle\ChannelBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\IntegratedBundle\Controller\PaginationQueryTrait;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Form\Type\DefinitionFormType;
use Integrated\Bundle\WorkflowBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\WorkflowBundle\Utils\StateVisibleConfig;
use Integrated\Common\Security\PermissionInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkflowController extends AbstractController
{
    use PaginationQueryTrait;

    private EntityManager $entityManager;
    private DocumentManager $documentManager;
    private PaginatorInterface $paginator;
    private UserManagerInterface $userManager;

    public function __construct(EntityManager $entityManager, DocumentManager $documentManager, PaginatorInterface $paginator, UserManagerInterface $userManager)
    {
        $this->entityManager = $entityManager;
        $this->documentManager = $documentManager;
        $this->paginator = $paginator;
        $this->userManager = $userManager;
    }

    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $pager = $this->paginator->paginate(
            $this->entityManager->getRepository('Integrated\Bundle\WorkflowBundle\Entity\Definition')->createQueryBuilder('item'),
            $this->getPositiveIntQueryParameter($request, 'page', 1),
            15
        );

        return $this->render('@IntegratedWorkflow/workflow/index.html.twig', ['pager' => $pager]);
    }

    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var Form $form */
        $form = $this->createNewForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_workflow_index');
            }

            if ($form->isValid()) {
                $workflow = $form->getData();

                $this->entityManager->persist($workflow);
                $this->entityManager->flush();

                return $this->redirectToRoute('integrated_workflow_index');
            }
        }

        return $this->render('@IntegratedWorkflow/workflow/new.html.twig', ['form' => $form]);
    }

    public function edit(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var Definition $workflow */
        $workflow = $this->entityManager
            ->getRepository(Definition::class)
            ->find($request->get('id'));

        if (!$workflow) {
            throw $this->createNotFoundException();
        }

        /** @var Form $form */
        $form = $this->createEditForm($workflow);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_workflow_index');
            }

            if ($form->isValid()) {
                $this->entityManager->flush();

                $this->addFlash('success', \sprintf('The changes to the workflow %s are saved', $workflow->getName()));

                return $this->redirectToRoute('integrated_workflow_edit', ['id' => $workflow->getId()]);
            }
        }

        return $this->render('@IntegratedWorkflow/workflow/edit.html.twig', [
            'workflow' => $workflow,
            'form' => $form,
        ]);
    }

    public function delete(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var Definition $workflow */
        $workflow = $this->entityManager->getRepository('Integrated\Bundle\WorkflowBundle\Entity\Definition')->find($request->get('id'));

        if (!$workflow) {
            return $this->redirectToRoute('integrated_workflow_index'); // workflow is already gone
        }

        /** @var Form $form */
        $form = $this->createDeleteForm($workflow);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_workflow_index');
            }

            if ($form->isValid()) {
                $this->entityManager->remove($workflow);
                $this->entityManager->flush();

                $this->addFlash('success', \sprintf('The workflow %s is removed', $workflow->getName()));

                return $this->redirectToRoute('integrated_workflow_index');
            }
        }

        return $this->render('@IntegratedWorkflow/workflow/delete.html.twig', [
            'workflow' => $workflow,
            'form' => $form,
        ]);
    }

    public function changeState(Request $request): Response
    {
        $stateId = $request->get('state');

        $isDefaultState = false;

        if (empty($stateId)) {
            $workflowId = $request->get('workflow');
            $repository = $this->entityManager->getRepository(Definition::class);
            $workflow = $repository->find($workflowId);
            if (!$workflow instanceof Definition) {
                return new JsonResponse(['users' => [], 'fields' => []]);
            }
            $state = $workflow->getDefault();

            $isDefaultState = true;
        } else {
            $repository = $this->entityManager->getRepository(Definition\State::class);
            $state = $repository->find($stateId);
        }

        if (!$state) {
            return new JsonResponse(['users' => [], 'fields' => []]);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $currentUserGroups = [];
        /** @var Group $group */
        foreach ($currentUser->getGroups() as $group) {
            $currentUserGroups[] = $group->getId();
        }

        $groups = [];
        $currentUserCanWrite = false;

        $permissionObject = false;
        if (\count($state->getPermissions()) > 0) {
            $permissionObject = $state;
        } else {
            // permissions inherited from content type
            $contentType = $this->documentManager->getRepository(ContentType::class)->find($request->get('contentType'));
            if ($contentType && \count($contentType->getPermissions()) > 0) {
                $permissionObject = $contentType;
            }
        }

        // use workflow permissions
        if ($permissionObject) {
            foreach ($permissionObject->getPermissions() as $permission) {
                if ($permission->getMask() >= PermissionInterface::WRITE) {
                    $group = $permission->getGroup();
                    $groups[] = $group;

                    if (\in_array($group, $currentUserGroups)) {
                        $currentUserCanWrite = true;
                    }
                }
            }
        }

        $queryBuilder = $this->entityManager->getRepository($this->userManager->getClassName())->createQueryBuilder('u');

        $queryBuilder->join('u.scope', 'us');
        $queryBuilder->where('us.admin = 1');

        if ($permissionObject && (!$isDefaultState || !$currentUserCanWrite)) {
            if (!$groups) {
                $queryBuilder->andWhere('1 = 0');
            } else {
                $queryBuilder->join('u.groups', 'ug');
                $queryBuilder->andWhere('ug.id IN (:groups)')->setParameter('groups', $groups);
            }
        }

        $users = [];
        /** @var User $item */
        foreach ($queryBuilder->getQuery()->getResult() as $item) {
            if ($item->getRelation() instanceof Person) {
                $users[] = [
                    'id' => $item->getId(),
                    'name' => $item->getRelation()->getFirstname().' '.$item->getRelation()->getLastName(),
                ];
            } else {
                $users[] = [
                    'id' => $item->getId(),
                    'name' => $item->getUserIdentifier(),
                ];
            }
        }

        $fieldsCodes = [
            'comment' => [
                'required' => $state->getComment() == StateVisibleConfig::REQUIRED,
                'disabled' => $state->getComment() == StateVisibleConfig::DISABLED,
            ],
            'assigned-choice' => [
                'required' => $state->getAssignee() == StateVisibleConfig::REQUIRED,
                'disabled' => $state->getAssignee() == StateVisibleConfig::DISABLED,
            ],
            'deadline' => [
                'required' => $state->getDeadline() == StateVisibleConfig::REQUIRED,
                'disabled' => $state->getDeadline() == StateVisibleConfig::DISABLED,
            ],
        ];

        $nextStates = [];
        $currentStateId = (string) $state->getId();
        $seenTransitions = [];
        foreach ($state->getTransitions() as $transition) {
            $transitionId = (string) $transition->getId();
            if ('' === $transitionId || $transitionId === $currentStateId || isset($seenTransitions[$transitionId])) {
                continue;
            }

            $seenTransitions[$transitionId] = true;
            $nextStates[] = [
                'id' => $transitionId,
                'name' => $transition->getName(),
            ];
        }

        usort($users, function ($a, $b) {
            return $a['name'] > $b['name'];
        });

        return new JsonResponse([
            'users' => $users,
            'fields' => $fieldsCodes,
            'next_states' => $nextStates,
        ]);
    }

    private function createNewForm(): Form
    {
        $form = $this->createForm(DefinitionFormType::class, null, [
            'action' => $this->generateUrl('integrated_workflow_new'),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    private function createEditForm(Definition $workflow): Form
    {
        $form = $this->createForm(DefinitionFormType::class, $workflow, [
            'action' => $this->generateUrl('integrated_workflow_edit', ['id' => $workflow->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    private function createDeleteForm(Definition $workflow): Form
    {
        $form = $this->createForm(DeleteFormType::class, $workflow, [
            'action' => $this->generateUrl('integrated_workflow_delete', ['id' => $workflow->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form;
    }
}
