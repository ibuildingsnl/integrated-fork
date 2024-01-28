<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Solr\Query;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Common\Solr\Search\Type\AbstractTypeExtension;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypesExtension extends AbstractTypeExtension
{
    public function __construct(private readonly DocumentManager $manager)
    {
    }

    public function build(Query $query, array $options): void
    {
        $contentTypes = $options['contenttypes'];

        if ($options['relation']) {
            $contentTypes = [];

            /** @var Relation $relation */
            if ($relation = $this->manager->getRepository(Relation::class)->find($options['relation'])) {
                foreach ($relation->getTargets() as $target) {
                    $contentTypes[] = $target->getId();
                }
            }
        }

        if (is_array($contentTypes) && \count($contentTypes)) {
            $helper = $query->getHelper();
            $filter = function ($param) use ($helper) {
                return $helper->escapePhrase($param);
            };

            $contentTypesQuery = $query->createFilterQuery('contenttypes')->addTag('contenttypes');
            $contentTypesQuery->setQuery('type_name: ((%1%))', [implode(') OR (', array_map($filter, $contentTypes))]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'relation' => null,
            'contenttypes' => [],
        ]);

        $resolver->addAllowedTypes('relation', ['null', 'string']);
        $resolver->addAllowedTypes('contenttypes', ['null', 'array']);
    }

    public static function getTypes(): iterable
    {
        return [
            IntegratedContent::class,
        ];
    }
}
