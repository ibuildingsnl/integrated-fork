<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Solr\Query;

use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Bundle\UserBundle\Model\GroupableInterface;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Solr\Search\Type\AbstractTypeExtension;
use Solarium\Component\Facet\Field;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowExtension extends AbstractTypeExtension
{
    private \Symfony\Bundle\SecurityBundle\Security $security;

    public function __construct(\Symfony\Bundle\SecurityBundle\Security $security)
    {
        $this->security = $security;
    }

    public function build(Query $query, array $options): void
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $query->createFilterQuery('workflow')
                ->addTag('workflow')
                ->addTag('security')
                ->setQuery($this->getSecurityQuery());
        }

        // handle facets

        $facet = $query->getFacetSet();
        $facet->setMinCount(1);

        /** @var Field $facetField */
        $facetField = $facet->createFacetField('workflow_state');

        $facetField->setField('facet_workflow_state')
            ->getLocalParameters()->setExclude('workflow_state');

        /** @var Field $facetField */
        $facetField = $facet->createFacetField('workflow_assigned');
        $facetField->setField('facet_workflow_assigned')
            ->getLocalParameters()->setExclude('workflow_assigned');

        $helper = $query->getHelper();
        $escape = function ($param) use ($helper) {
            return $helper->escapePhrase($param);
        };

        if ($options['workflow_state']) {
            $query->createFilterQuery('workflow_state')
                ->addTag('workflow_state')
                ->setQuery('facet_workflow_state: ((%1%))', [implode(') OR (', array_map($escape, $options['workflow_state']))]);
        }

        if ($options['workflow_assigned']) {
            $query->createFilterQuery('workflow_assigned')
                ->addTag('workflow_assigned')
                ->setQuery('facet_workflow_assigned: ((%1%))', [implode(') OR (', array_map($escape, $options['workflow_assigned']))]);
        }
    }

    private function getSecurityQuery(): string
    {
        $query = [];
        $query[] = '(*:* -security_workflow_read:[* TO *])'; // allow content without workflow

        $user = $this->security->getUser();

        if ($user instanceof GroupableInterface) {
            $groups = [];

            foreach ($user->getGroups() as $group) {
                $groups[] = $group->getId();
            }

            // allow content with group access

            if ($groups) {
                $query[] = \sprintf('security_workflow_read: ((%s))', implode(' ) OR (', $groups));
            }
        }

        if ($user instanceof UserInterface) {
            // always allow access to assigned content
            $query[] = \sprintf('facet_workflow_assigned_id: %s', $user->getId());
        }

        if ($user instanceof User) {
            if ($person = $user->getRelation()) {
                $query[] = \sprintf('author: %s', $person->getId());
            }
        }

        return implode(' OR ', $query);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'workflow_state' => [],
            'workflow_assigned' => [],
        ]);

        $arrayNormalizer = function (Options $options, $value) {
            if (!\is_array($value)) {
                return [];
            }

            return $this->sanitizeListValues($value);
        };

        $resolver->setNormalizer('workflow_state', $arrayNormalizer);
        $resolver->setNormalizer('workflow_assigned', $arrayNormalizer);
    }

    public static function getTypes(): iterable
    {
        return [
            IntegratedContent::class,
        ];
    }

    private function sanitizeListValues(array $values): array
    {
        $sanitized = [];

        foreach ($values as $value) {
            if (!\is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ($value !== '') {
                $sanitized[] = $value;
            }
        }

        return $sanitized;
    }
}
