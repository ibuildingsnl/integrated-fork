<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State;

class StateManager
{
    private const FLUSH_BATCH_SIZE = 100;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * State constructor.
     */
    public function __construct(EntityManager $entityManager, DocumentManager $documentManager)
    {
        $this->entityManager = $entityManager;
        $this->documentManager = $documentManager;
    }

    /**
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ensureWorkflowState(string $contentType): void
    {
        $contentType = $this->documentManager->getRepository(ContentType::class)->find($contentType);

        if (!$contentType || !$contentType->hasOption('workflow')) {
            return;
        }

        $workflow = $this->entityManager->getRepository(Definition::class)->find($contentType->getOption('workflow'));
        if (!$workflow) {
            return;
        }

        $stateRepository = $this->entityManager->getRepository(State::class);

        $contentIds = $this->documentManager->createQueryBuilder(Content::class)
            ->select('_id', 'class')
            ->field('contentType')->equals($contentType->getId())
            ->hydrate(false)
            ->getQuery()
            ->execute();

        $entityDirty = false;
        $documentDirty = false;
        $batchCount = 0;

        foreach ($contentIds as $item) {
            $content = $this->documentManager->getRepository(Content::class)->find($item['_id']);
            if (!$content instanceof Content) {
                continue;
            }

            $state = $stateRepository->findOneBy(['content_id' => $item['_id'], 'content_class' => $item['class']]);

            if (!$state instanceof State) {
                $initialState = $this->resolveInitialState($workflow, $content);
                if (!$initialState instanceof Definition\State) {
                    continue;
                }

                $state = new State();
                $state->setContent($content);
                $state->setState($initialState);

                $this->entityManager->persist($state);
                $entityDirty = true;
            }

            if ($this->syncContentWithState($content, $workflow, $state->getState())) {
                $documentDirty = true;
            }

            ++$batchCount;
            if ($batchCount >= self::FLUSH_BATCH_SIZE) {
                $this->flushPending($entityDirty, $documentDirty);
                $batchCount = 0;
            }
        }

        $this->flushPending($entityDirty, $documentDirty);
    }

    private function resolveInitialState(Definition $workflow, Content $content): ?Definition\State
    {
        $defaultState = $workflow->getDefault();
        if ($defaultState !== null && $this->isStateCompatibleWithContent($defaultState, $content)) {
            return $defaultState;
        }

        foreach ($workflow->getStates() as $state) {
            if ($this->isStateCompatibleWithContent($state, $content)) {
                return $state;
            }
        }

        return $defaultState;
    }

    private function isStateCompatibleWithContent(Definition\State $state, Content $content): bool
    {
        return (bool) $content->isDisabled() !== $state->isPublishable();
    }

    private function syncContentWithState(Content $content, Definition $workflow, ?Definition\State $state): bool
    {
        if (!$state instanceof Definition\State) {
            return false;
        }

        $changed = false;

        if ($content->getMetadata()->get('workflow') !== $workflow->getId()) {
            $content->getMetadata()->set('workflow', $workflow->getId());
            $changed = true;
        }

        if ($content->getMetadata()->get('workflow_state') !== $state->getId()) {
            $content->getMetadata()->set('workflow_state', $state->getId());
            $changed = true;
        }

        $disabled = !$state->isPublishable();
        if ((bool) $content->isDisabled() !== $disabled) {
            $content->setDisabled($disabled);
            $changed = true;
        }

        if ($changed) {
            $this->documentManager->persist($content);
        }

        return $changed;
    }

    private function flushPending(bool &$entityDirty, bool &$documentDirty): void
    {
        if ($entityDirty) {
            $this->entityManager->flush();
            $entityDirty = false;
        }

        if ($documentDirty) {
            $this->documentManager->flush();
            $documentDirty = false;
        }
    }
}
