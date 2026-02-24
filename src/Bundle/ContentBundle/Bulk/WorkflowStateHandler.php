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
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State as WorkflowState;
use Integrated\Common\Bulk\Action\HandlerInterface;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\MetadataInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class WorkflowStateHandler implements HandlerInterface
{
    private const NAVDROPDOWNS_CACHE_NAMESPACE = 'integrated_content_fragments_navdropdowns';

    private EntityManagerInterface $entityManager;
    private ResolverInterface $resolver;
    private string $stateId;
    private ?Definition\State $targetState = null;
    private bool $targetStateResolved = false;
    private bool $navdropdownCacheInvalidated = false;

    /**
     * @var Definition[]
     */
    private array $workflowCache = [];

    public function __construct(EntityManagerInterface $entityManager, ResolverInterface $resolver, string $stateId)
    {
        $this->entityManager = $entityManager;
        $this->resolver = $resolver;
        $this->stateId = $stateId;
    }

    public function execute(ContentInterface $content)
    {
        $targetState = $this->resolveTargetState();
        $workflow = $this->resolveWorkflow($content);

        if (!$targetState instanceof Definition\State || !$workflow instanceof Definition) {
            return;
        }

        if ($targetState->getWorkflow()?->getId() !== $workflow->getId()) {
            return;
        }

        $repository = $this->entityManager->getRepository(WorkflowState::class);
        $state = $repository->findOneBy(['content' => $content]);

        if (!$state instanceof WorkflowState) {
            $state = new WorkflowState();
            $state->setContent($content);

            $this->entityManager->persist($state);
        }

        if ($state->getState() && $state->getState()->getId() === $targetState->getId()) {
            return;
        }

        $state->setState($targetState);

        if ($content instanceof MetadataInterface) {
            $content->getMetadata()->set('workflow', $workflow->getId());
            $content->getMetadata()->set('workflow_state', $targetState->getId());
        }

        if (method_exists($content, 'setDisabled')) {
            $content->setDisabled(!$targetState->isPublishable());
        }

        $this->entityManager->flush();
        $this->invalidateNavdropdownCache();
    }

    private function resolveTargetState(): ?Definition\State
    {
        if ($this->targetStateResolved) {
            return $this->targetState;
        }

        $this->targetStateResolved = true;
        $state = $this->entityManager->getRepository(Definition\State::class)->find($this->stateId);

        if ($state instanceof Definition\State) {
            $this->targetState = $state;
        }

        return $this->targetState;
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

