<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Solr\Query\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Common\Solr\Search\Type\AbstractType;
use Solarium\Component\Facet\Field;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegratedContentBlock extends AbstractType
{
    private DocumentManager $manager;

    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    public function build(Query $query, array $options): void
    {
        $helper = $query->getHelper();
        $escape = function ($param) use ($helper) {
            return $helper->escapePhrase($param);
        };

        if ($options['q']) {
            $query->getEDisMax()
                ->setQueryFields('title^200 content subtitle intro')
                ->setMinimumMatch('75%');

            $query->setQuery($options['q']);
        }

        if ($options['exclude'] && $options['exclude_ids']) {
            $query->setQuery($query->getQuery().' AND -type_id: (%1%)', [implode(' OR ', array_map($escape, $options['exclude_ids']))]);
        }

        $facet = $query->getFacetSet();

        foreach ($options['facets'] as $field => $value) {
            /** @var Field $facetField */
            $facetField = $facet->createFacetField($field);
            $facetField->setField($field)
                ->setMinCount(1)
                ->getLocalParameters()->setExclude($field);

            if ($value) {
                $query
                    ->createFilterQuery($field)
                    ->setQuery($field.': ((%1%))', [implode(') OR (', array_map($escape, $value))])
                    ->addTag($field);
            }
        }

        foreach ($options['facets_search_selection'] as $field => $value) {
            /** @var Field $facetField */
            $facetField = $facet->createFacetField($field.'_search_selection');
            $facetField->setField($field)
                ->setMinCount(1);

            if ($value) {
                $query
                    ->createFilterQuery($field.'_search_selection')
                    ->setQuery($field.': ((%1%))', [implode(') OR (', array_map($escape, $value))])
                    ->addTag($field.'_search_selection');
            }
        }

        foreach ($options['params'] as $key => $value) {
            $query->addParam($key, $value);
        }

        foreach ($this->manager->getRepository(Relation::class)->findAll() as $relation) {
            /** @var Field $facetField */
            $facetField = $facet->createFacetField($name = 'relation_'.$relation->getId().'_search_selection');
            $facetField->setField($field = 'facet_'.$relation->getId());

            if ($value = $options['relation_search_selection'][$relation->getId()] ?? []) {
                $query->createFilterQuery($name)
                    ->addTag($name)
                    ->setQuery($field.': ((%1%))', [implode(') OR (', array_map($escape, $value))]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'exclude' => false,
            'exclude_ids' => [],
            'params' => [],
            'facets' => [],
            'facets_search_selection' => [],
        ]);

        $resolver->setAllowedTypes('exclude', 'boolean');

        $arrayNormalizer = function (Options $options, $value) {
            if (\is_array($value)) {
                return array_filter(array_map('trim', $value));
            }

            return [];
        };

        $resolver->setNormalizer('exclude_ids', $arrayNormalizer);

        $resolver->setNormalizer('params', function (Options $options, $values) {
            $filters = [];

            if (!\is_array($values)) {
                return $filters;
            }

            foreach ($values as $key => $value) {
                $key = trim($key);
                $value = trim($value);

                if ($key && $value) {
                    $filters[$key] = $value;
                }
            }

            return $filters;
        });

        $resolver->setDefaults([
            'relation_search_selection' => [],
        ]);

        $resolver->setNormalizer('relation_search_selection', function (Options $options, $values) {
            $relations = [];

            if (!\is_array($values)) {
                return $relations;
            }

            $allowed = [];

            foreach ($this->manager->getRepository(Relation::class)->findAll() as $relation) {
                $allowed[] = $relation->getId();
            }

            foreach ($values as $key => $value) {
                if (!\is_array($value)) {
                    continue;
                }

                $key = trim($key);

                if (!\in_array($key, $allowed)) {
                    continue;
                }

                $relations[$key] = array_filter(array_map('trim', $value));
            }

            return array_filter($relations);
        });
    }

    public function getParent(): ?string
    {
        return Content::class;
    }
}
