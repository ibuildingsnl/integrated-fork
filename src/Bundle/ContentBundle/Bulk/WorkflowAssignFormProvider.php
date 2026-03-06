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

use Integrated\Bundle\ContentBundle\Form\Type\BulkActionWorkflowAssignType;
use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Integrated\Common\Bulk\Form\Config;
use Integrated\Common\Bulk\Form\ConfigProviderInterface;

class WorkflowAssignFormProvider implements ConfigProviderInterface
{
    /**
     * @var BulkCapabilityResolver
     */
    private $capabilityResolver;

    private UserManager $userManager;

    public function __construct(BulkCapabilityResolver $capabilityResolver, UserManager $userManager)
    {
        $this->capabilityResolver = $capabilityResolver;
        $this->userManager = $userManager;
    }

    public function getConfig(array $content)
    {
        if (!$this->capabilityResolver->supports($content, 'workflow')) {
            return [];
        }

        return [
            new Config(
                WorkflowAssignHandler::class,
                'workflowAssign',
                BulkActionWorkflowAssignType::class,
                [
                    'workflow_assign_handler' => WorkflowAssignHandler::class,
                    'user_choices' => $this->getUserChoices(),
                    'label' => 'Assignee',
                ],
                new BulkActionOptionMatcher(WorkflowAssignHandler::class, 'assigned')
            ),
        ];
    }

    /**
     * @return string[]
     */
    private function getUserChoices(): array
    {
        $builder = $this->userManager->createQueryBuilder();
        $builder->join('User.scope', 'scope');
        $builder->where('scope.admin = 1');

        $choices = [];
        foreach ($builder->getQuery()->getArrayResult() as $row) {
            if (!isset($row['username'], $row['id'])) {
                continue;
            }

            $choices[(string) $row['username']] = (string) $row['id'];
        }

        ksort($choices);

        return $choices;
    }
}
