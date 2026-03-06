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
use Solarium\Component\Facet\Field;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelationExtension extends AbstractTypeExtension
{
    public function __construct(private readonly DocumentManager $manager)
    {
    }

    public function build(Query $query, array $options): void
    {
        $facet = $query->getFacetSet();
        $facet->setMinCount(1);

        $helper = $query->getHelper();
        $escape = function ($param) use ($helper) {
            return $helper->escapePhrase($param);
        };

        foreach ($this->manager->getRepository(Relation::class)->findAll() as $relation) {
            /** @var Field $facetField */
            $facetField = $facet->createFacetField($name = 'relation_'.$relation->getId());
            $facetField->setField($field = 'facet_'.$relation->getId())
                ->getLocalParameters()->setExclude($name);

            $values = $this->sanitizeListValues($options['relation'][$relation->getId()] ?? []);
            if (\count($values)) {
                $query->createFilterQuery($name)
                    ->addTag($name)
                    ->setQuery($field.': ((%1%))', [implode(') OR (', array_map($escape, $values))]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'relation' => [],
        ]);

        $resolver->setNormalizer('relation', function (Options $options, $values) {
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

                $relations[$key] = $this->sanitizeListValues($value);
            }

            return array_filter($relations);
        });
    }

    public static function getTypes(): iterable
    {
        return [
            IntegratedContent::class,
        ];
    }

    /**
     * @param array<mixed> $values
     *
     * @return list<string>
     */
    private function sanitizeListValues(array $values): array
    {
        $sanitized = [];

        foreach ($values as $value) {
            if (!\is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ('' !== $value) {
                $sanitized[] = $value;
            }
        }

        return $sanitized;
    }
}
