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
use Integrated\Bundle\ContentBundle\Services\AssignedStatusCacheInvalidator;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State as WorkflowState;
use Integrated\Common\Bulk\Action\HandlerInterface;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\MetadataInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class WorkflowAssignHandler implements HandlerInterface
{
    private const NAVDROPDOWNS_CACHE_NAMESPACE = 'integrated_content_fragments_navdropdowns';

    private EntityManagerInterface $entityManager;
    private ResolverInterface $resolver;
    private UserManagerInterface $userManager;
    private AssignedStatusCacheInvalidator $assignedStatusCacheInvalidator;
    private ?string $assignedId;
    private bool $navdropdownCacheInvalidated = false;
    private bool $assignedResolved = false;
    private bool $assignedInvalid = false;
    private ?UserInterface $assignedUser = null;

    /** @var array<string, Definition|null> */
    private array $workflowCache = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        ResolverInterface $resolver,
        UserManagerInterface $userManager,
        AssignedStatusCacheInvalidator $assignedStatusCacheInvalidator,
        ?string $assignedId,
    ) {
        $this->entityManager = $entityManager;
        $this->resolver = $resolver;
        $this->userManager = $userManager;
        $this->assignedStatusCacheInvalidator = $assignedStatusCacheInvalidator;
        $this->assignedId = $assignedId;
    }

    public function execute(ContentInterface $content): void
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
        $flush = false;

        if (!$state instanceof WorkflowState) {
            $initialState = $this->resolveInitialState($workflow, $content);
            if (!$initialState instanceof Definition\State) {
                return;
            }

            $state = new WorkflowState();
            $state->setContent($content);
            $state->setState($initialState);

            $this->entityManager->persist($state);
            $flush = true;
        }

        $currentAssigned = $state->getAssigned();
        if ($currentAssigned instanceof UserInterface && $assigned instanceof UserInterface) {
            if ($currentAssigned->getId() === $assigned->getId()) {
                $this->syncContentWithState($content, $workflow, $state->getState());

                if ($flush) {
                    $this->entityManager->flush();
                    $this->invalidateNavdropdownCache();
                }

                return;
            }
        } elseif (null === $currentAssigned && null === $assigned) {
            $this->syncContentWithState($content, $workflow, $state->getState());

            if ($flush) {
                $this->entityManager->flush();
                $this->invalidateNavdropdownCache();
            }

            return;
        }

        $affectedAssignedUserIds = $this->getAffectedAssignedUserIds($currentAssigned, $assigned);
        $state->setAssigned($assigned);
        $this->syncContentWithState($content, $workflow, $state->getState());
        $this->entityManager->flush();
        $this->assignedStatusCacheInvalidator->invalidateUsers($affectedAssignedUserIds);
        $this->invalidateNavdropdownCache();
    }

    /**
     * @return array<int, string|null>
     */
    private function getAffectedAssignedUserIds(?UserInterface $currentAssigned, ?UserInterface $assigned): array
    {
        return [
            $currentAssigned instanceof UserInterface ? (string) $currentAssigned->getId() : null,
            $assigned instanceof UserInterface ? (string) $assigned->getId() : null,
        ];
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

        if (!\array_key_exists($workflowId, $this->workflowCache)) {
            $this->workflowCache[$workflowId] = $this->entityManager->getRepository(Definition::class)->find($workflowId);
        }

        $workflow = $this->workflowCache[$workflowId];

        return $workflow instanceof Definition ? $workflow : null;
    }

    private function resolveInitialState(Definition $workflow, ContentInterface $content): ?Definition\State
    {
        $default = $workflow->getDefault();
        if ($default !== null && $this->isStateCompatibleWithContent($default, $content)) {
            return $default;
        }

        foreach ($workflow->getStates() as $state) {
            if ($this->isStateCompatibleWithContent($state, $content)) {
                return $state;
            }
        }

        return $default;
    }

    private function isStateCompatibleWithContent(Definition\State $state, ContentInterface $content): bool
    {
        if (!method_exists($content, 'isDisabled')) {
            return true;
        }

        return (bool) $content->isDisabled() !== $state->isPublishable();
    }

    private function syncContentWithState(ContentInterface $content, Definition $workflow, ?Definition\State $state): void
    {
        if (!$state instanceof Definition\State) {
            return;
        }

        if ($content instanceof MetadataInterface) {
            $content->getMetadata()->set('workflow', $workflow->getId());
            $content->getMetadata()->set('workflow_state', $state->getId());
        }

        if (method_exists($content, 'setDisabled')) {
            $content->setDisabled(!$state->isPublishable());
        }
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
