<?php

namespace Integrated\Bundle\ContentBundle\Solr\Query\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOptions;
use Integrated\Common\Solr\Search\Type\AbstractType;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Content extends AbstractType
{
    public function __construct(
        private readonly SortOptions $sorting,
        private readonly DocumentManager $manager
    ) {
    }

    public function build(Query $query, array $options): void
    {
        if ($options['q']) {
            $query->getEDisMax()
                ->setQueryFields('title content')
                ->setMinimumMatch('75%');

            $query->setQuery($options['q']);
        }

        $query->addSort($options['sort'], $options['order']);

        if ($options['ids']) {
            $query->createFilterQuery('ids')
                ->setQuery('type_id: ("%1%")', [implode('" OR "', $options['ids'])]);
        }

        // handle facets

        $facet = $query->getFacetSet();
        $facet->setMinCount(1);

        $facet->createFacetField('contenttypes')
            ->setField('type_name')
            ->getLocalParameters()->setExclude('contenttypes');

        $facet->createFacetField('channels')
            ->setField('facet_channels')
            ->getLocalParameters()->setExclude('channels');

        $facet->createFacetField('authors')
            ->setField('facet_authors')
            ->getLocalParameters()->setExclude('authors');

        $facet->createFacetField('properties')
            ->setField('facet_properties')
            ->getLocalParameters()->setExclude('properties');

        $helper = $query->getHelper();
        $escape = function ($param) use ($helper) {
            return $helper->escapePhrase($param);
        };

        if ($options['contenttypes']) {
            $query->createFilterQuery('contenttypes')
                ->addTag('contenttypes')
                ->setQuery('type_name: ((%1%))', [implode(') OR (', array_map($escape, $options['contenttypes']))]);
        }

        if ($options['channels']) {
            $query->createFilterQuery('channels')
                ->addTag('channels')
                ->setQuery('facet_channels: ((%1%))', [implode(') OR (', array_map($escape, $options['channels']))]);
        }
//@TODO: Add publication_start_date to solr
//        if ($options['pub_channels']) {
//            foreach ($options['pub_channels'] as $channel) {
//                $channel = $helper->escapeTerm($channel);
//                $query->createFilterQuery('pub_channel_'.$channel)
//                      ->setQuery('(publication_start_'.$channel.'_index_date: [* TO NOW]) AND (publication_end_'.$channel.'_index_date: [NOW TO *])');
//            }
//        }

        if ($options['authors']) {
            $query->createFilterQuery('authors')
                ->addTag('authors')
                ->setQuery('facet_authors: ((%1%))', [implode(') OR (', array_map($escape, $options['authors']))]);
        }

        if ($options['properties']) {
            $query->createFilterQuery('properties')
                ->addTag('properties')
                ->setQuery('facet_properties: ((%1%))', [implode(') OR (', array_map($escape, $options['properties']))]);
        }

        // handler filters

        foreach ($options['filter'] as $field => $value) {
            $query->createFilterQuery($field)->setQuery('%1%:%P2%', [$field, $value]);
        }

        // handle relations

        foreach ($this->manager->getRepository(Relation::class)->findAll() as $relation) {
            $facet->createFacetField($name = 'relation_'.$relation->getId())
                ->setField($field = 'facet_'.$relation->getId())
                ->getLocalParameters()->setExclude($name);

            if ($options['relation'][$relation->getId()] ?? []) {
                $query->createFilterQuery($name)
                    ->addTag($name)
                    ->setQuery($field.': ((%1%))', [implode(') OR (', array_map($escape, $options['relation'][$relation->getId()]))]);
            }
        }

        // handle start/end dates
        if ($options['start'] instanceof \DateTimeInterface && $options['end'] instanceof \DateTimeInterface) {
            $query->createFilterQuery('pub_time')
                ->addTag('pub_time')
                ->setQuery(sprintf(
                    'pub_time: [%s TO %s]',
                    $options['start']->format("Y-m-d\TH:i:s.z\Z"),
                    $options['end']->format("Y-m-d\TH:i:s.z\Z"),
                ));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'q' => '',
            'sort' => '',
            'order' => '',
            'ids' => '',
        ]);

        $resolver->setNormalizer('q', function (Options $options, $value) {
            return trim($value);
        });

        $resolver->setNormalizer('sort', function (Options $options, $value) {
            $value = strtolower(trim($value));

            if ($this->sorting->hasByField($value)) {
                // rel is only allowed if there is a query
                if ($value !== 'rel' || $options['q']) {
                    return $this->sorting->getByField($value)->field;
                }
            }

            if ($options['q']) {
                return $this->sorting->get('rel')->field;
            }

            return $this->sorting->get('time')->field;
        });

        $resolver->setNormalizer('order', function (Options $options, $value) {
            $value = strtolower(trim($value));

            if (\is_string($value) && \in_array($value, ['asc', 'desc'])) {
                return $value;
            }

            return $this->sorting->getByField($options['sort'])->order;
        });

        $resolver->setNormalizer('ids', function (Options $options, $value) {
            if (\is_string($value)) {
                $value = explode(',', $value);
            }

            $value = array_map('trim', $value);

            return array_filter($value, function (string $value) {
                return preg_match('/[a-z0-9]{32}/', $value);
            });
        });

        $resolver->setDefaults([
            'contenttypes' => [],
            'channels' => [],
            'authors' => [],
            'pub_channels' => [],
            'properties' => [],
        ]);

        $arrayNormalizer = function (Options $options, $value) {
            if (\is_array($value)) {
                return array_filter(array_map('trim', $value));
            }

            return [];
        };

        $resolver->setNormalizer('contenttypes', $arrayNormalizer);
        $resolver->setNormalizer('channels', $arrayNormalizer);
        $resolver->setNormalizer('authors', $arrayNormalizer);
        $resolver->setNormalizer('properties', $arrayNormalizer);

        // handle filters that will be directly inserted into the query base on a key value
        $resolver->setDefaults([
            'filter' => [],
        ]);

        $resolver->setNormalizer('filter', function (Options $options, $values) {
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

        // handle relations
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

                $relations[$key] = array_filter(array_map('trim', $value));
            }

            return array_filter($relations);
        });


        // handle start/end dates
        $resolver->setDefaults([
            'start' => null,
            'end' => null,
        ]);
    }
}
