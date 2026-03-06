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
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionWorkflowStateType;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Common\Bulk\Form\Config;
use Integrated\Common\Bulk\Form\ConfigProviderInterface;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\ContentType\ResolverInterface;

class WorkflowStateFormProvider implements ConfigProviderInterface
{
    /**
     * @var BulkCapabilityResolver
     */
    private $capabilityResolver;

    private ResolverInterface $resolver;
    private EntityManagerInterface $entityManager;

    public function __construct(
        BulkCapabilityResolver $capabilityResolver,
        ResolverInterface $resolver,
        EntityManagerInterface $entityManager,
    ) {
        $this->capabilityResolver = $capabilityResolver;
        $this->resolver = $resolver;
        $this->entityManager = $entityManager;
    }

    public function getConfig(array $content)
    {
        if (!$this->capabilityResolver->supports($content, 'workflow')) {
            return [];
        }

        $choices = $this->getSharedStateChoices($content);
        if (!$choices) {
            return [];
        }

        return [
            new Config(
                WorkflowStateHandler::class,
                'workflowState',
                BulkActionWorkflowStateType::class,
                [
                    'workflow_state_handler' => WorkflowStateHandler::class,
                    'state_choices' => $choices,
                    'label' => 'Workflow status',
                ],
                new BulkActionOptionMatcher(WorkflowStateHandler::class, 'state')
            ),
        ];
    }

    /**
     * @param array<ContentInterface> $content
     *
     * @return array<string, string>
     */
    private function getSharedStateChoices(array $content): array
    {
        $shared = null;

        foreach ($content as $item) {
            $contentType = (string) $item->getContentType();
            if ('' === $contentType || !$this->resolver->hasType($contentType)) {
                return [];
            }

            $type = $this->resolver->getType($contentType);
            $workflowId = (string) $type->getOption('workflow');
            if ('' === $workflowId) {
                return [];
            }

            $workflow = $this->entityManager->getRepository(Definition::class)->find($workflowId);
            if (!$workflow instanceof Definition) {
                return [];
            }

            $choices = [];
            foreach ($workflow->getStates() as $state) {
                $choices[$state->getId()] = $state->getName();
            }

            if (null === $shared) {
                $shared = $choices;
                continue;
            }

            $shared = array_intersect_key($shared, $choices);
        }

        if (!$shared) {
            return [];
        }

        asort($shared);

        $result = [];
        foreach ($shared as $stateId => $stateName) {
            $result[$stateName] = $stateId;
        }

        return $result;
    }
}
