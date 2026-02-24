<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Bulk;

use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State as WorkflowState;
use Integrated\Common\Bulk\Action\HandlerInterface;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class WorkflowAssignHandler implements HandlerInterface
{
    private const NAVDROPDOWNS_CACHE_NAMESPACE = 'integrated_content_fragments_navdropdowns';

    private EntityManagerInterface $entityManager;
    private ResolverInterface $resolver;
    private UserManagerInterface $userManager;
    private ?string $assignedId;
    private bool $navdropdownCacheInvalidated = false;
    private bool $assignedResolved = false;
    private bool $assignedInvalid = false;
    private ?UserInterface $assignedUser = null;

    /**
     * @var Definition[]
     */
    private array $workflowCache = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        ResolverInterface $resolver,
        UserManagerInterface $userManager,
        ?string $assignedId
    ) {
        $this->entityManager = $entityManager;
        $this->resolver = $resolver;
        $this->userManager = $userManager;
        $this->assignedId = $assignedId;
    }

    public function execute(ContentInterface $content)
    {
        $workflow = $this->resolveWorkflow($content);
        if (!$workflow instanceof Definition) {
            return;
        }

        $assigned = $this->resolveAssignedUser();
        if ($this->assignedInvalid) {
            return;
        }

        $repository = $this->entityManager->getRepository(WorkflowState::class);
        $state = $repository->findOneBy(['content' => $content]);

        if (!$state instanceof WorkflowState) {
            $defaultState = $workflow->getDefault();
            if (!$defaultState instanceof Definition\State) {
                return;
            }

            $state = new WorkflowState();
            $state->setContent($content);
            $state->setState($defaultState);

            $this->entityManager->persist($state);
        }

        $currentAssigned = $state->getAssigned();
        if ($currentAssigned instanceof UserInterface && $assigned instanceof UserInterface) {
            if ($currentAssigned->getId() === $assigned->getId()) {
                return;
            }
        } elseif (null === $currentAssigned && null === $assigned) {
            return;
        }

        $state->setAssigned($assigned);

        $this->entityManager->flush();
        $this->invalidateNavdropdownCache();
    }

    private function resolveAssignedUser(): ?UserInterface
    {
        if ($this->assignedResolved) {
            return $this->assignedUser;
        }

        $this->assignedResolved = true;

        if (null === $this->assignedId || '' === $this->assignedId) {
            return null;
        }

        $assigned = $this->userManager->find($this->assignedId);
        if (!$assigned instanceof UserInterface) {
            $this->assignedInvalid = true;

            return null;
        }

        $this->assignedUser = $assigned;

        return $this->assignedUser;
    }

    private function resolveWorkflow(ContentInterface $content): ?Definition
    {
        $contentType = (string) $content->getContentType();

        if ('' === $contentType || !$this->resolver->hasType($contentType)) {
            return null;
        }

        $type = $this->resolver->getType($contentType);

        if (!$type->hasOption('workflow')) {
            return null;
        }

        $workflowId = (string) $type->getOption('workflow');
        if ('' === $workflowId) {
            return null;
        }

        if (!array_key_exists($workflowId, $this->workflowCache)) {
            $this->workflowCache[$workflowId] = $this->entityManager->getRepository(Definition::class)->find($workflowId);
        }

        $workflow = $this->workflowCache[$workflowId];

        return $workflow instanceof Definition ? $workflow : null;
    }

    private function invalidateNavdropdownCache(): void
    {
        if ($this->navdropdownCacheInvalidated) {
            return;
        }

        (new FilesystemAdapter(self::NAVDROPDOWNS_CACHE_NAMESPACE))->clear();
        $this->navdropdownCacheInvalidated = true;
    }
}

