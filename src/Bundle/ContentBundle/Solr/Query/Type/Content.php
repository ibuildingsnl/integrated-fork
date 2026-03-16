<?php

namespace Integrated\Bundle\ContentBundle\Solr\Query\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOptions;
use Integrated\Common\Solr\Search\Type\AbstractType;
use Solarium\Component\Facet\Field;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Content extends AbstractType
{
    public function __construct(
        private readonly SortOptions $sorting,
        private readonly DocumentManager $manager,
    ) {
    }

    public function build(Query $query, array $options): void
    {
        $contentChoiceBrands = [];
        if ($options['q']) {
            $query->getEDisMax()
                ->setQueryFields($this->getQueryFields($options))
                ->setMinimumMatch($this->getMinimumMatch($options));

            if ($this->isContentChoiceSearch($options)) {
                $contentChoiceBrands = $this->extractMatchingBrands($options['q']);
                $query->setQueryDefaultOperator(Query::QUERY_OPERATOR_AND);
                $query->setQuery($this->buildContentChoiceQuery($query, $options['q'], $contentChoiceBrands));
            } else {
                $query->setQuery($options['q']);
            }
        }

        $query->addSort($options['sort'], $options['order']);

        if ($options['ids']) {
            $query->createFilterQuery('ids')
                ->setQuery('type_id: ("%1%")', [implode('" OR "', $options['ids'])]);
        }

        // handle facets

        $facet = $query->getFacetSet();
        $facet->setMinCount(1);

        /** @var Field $facetField */
        $facetField = $facet->createFacetField('contenttypes');
        $facetField->setField('type_name')
            ->getLocalParameters()->setExclude('contenttypes');

        /** @var Field $facetField */
        $facetField = $facet->createFacetField('channels');
        $facetField->setField('facet_channels')
            ->getLocalParameters()->setExclude('channels');

        /** @var Field $facetField */
        $facetField = $facet->createFacetField('brands');
        $facetField->setField('facet_brands')
            ->getLocalParameters()->setExclude('brands');

        /** @var Field $facetField */
        $facetField = $facet->createFacetField('authors');
        $facetField->setField('facet_authors')
            ->getLocalParameters()->setExclude('authors');

        /** @var Field $facetField */
        $facetField = $facet->createFacetField('properties');
        $facetField->setField('facet_properties')
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

        if ($options['exclude_contenttypes']) {
            $query->createFilterQuery('excluded_contenttypes')
                ->setQuery('-type_name: ((%1%))', [implode(') OR (', array_map($escape, $options['exclude_contenttypes']))]);
        }

        if ($options['channels']) {
            $query->createFilterQuery('channels')
                ->addTag('channels')
                ->setQuery('facet_channels: ((%1%))', [implode(') OR (', array_map($escape, $options['channels']))]);
        }

        if ($contentChoiceBrands) {
            $query->createFilterQuery('content_choice_brands')
                ->setQuery('facet_brands: ((%1%))', [implode(') OR (', array_map($escape, array_keys($contentChoiceBrands)))]);
        }

        if ($options['pub_channels']) {
            foreach ($options['pub_channels'] as $channel) {
                $channel = $helper->escapeTerm($channel);
                $query->createFilterQuery('pub_channel_'.$channel)
                      ->setQuery('(publication_start_'.$channel.'_index_date: [* TO NOW]) AND (publication_end_'.$channel.'_index_date: [NOW TO *])');
            }
        }

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

        if ($created = $options['created']) {
            $from =
            $query
                ->createFilterQuery('pub_created')
                ->setQuery('pub_created: ['.$created['start'].' TO '.$created['end'].']');
        }

        // handler filters

        foreach ($options['filter'] as $field => $value) {
            $query->createFilterQuery($field)->setQuery('%1%:%P2%', [$field, $value]);
        }

        // handle start/end dates
        if ($options['start'] instanceof \DateTimeInterface && $options['end'] instanceof \DateTimeInterface) {
            $query->createFilterQuery('pub_time')
                ->addTag('pub_time')
                ->setQuery(\sprintf(
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
            'created' => null,
            'search_context' => '',
        ]);

        $resolver->setNormalizer('q', function (Options $options, $value) {
            return trim($value);
        });

        $resolver->setNormalizer('sort', function (Options $options, $value) {
            $value = strtolower(trim($value));

            if (str_starts_with($value, 'custom:')) {
                // Support custom sort fields (for example from search selections).
                $sortOption = explode(' ', $value, 2);

                return substr($sortOption[0], 7);
            }

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

            if ($options['sort'] === $this->sorting->get('rel')->field) {
                // Relevance sorting should always rank highest score first.
                return 'desc';
            }

            if (str_starts_with($value, 'custom:')) {
                // Support "custom:<field> <order>" value style.
                $sortOption = explode(' ', $value, 2);

                return $sortOption[1] ?? 'asc';
            }

            if (\is_string($value) && \in_array($value, ['asc', 'desc'])) {
                return $value;
            }

            if (!$this->sorting->hasByField($options['sort'])) {
                // Custom sort fields are not part of the default sort option list.
                return 'asc';
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

        $resolver->setNormalizer('created', function (Options $options, $value) {
            if (!\is_array($value)) {
                return null;
            }

            return [
                'start' => preg_replace('/[^0-9\*\-\:TZ]/', '', $value['start'] ?? '*'),
                'end' => preg_replace('/[^0-9\*\-\:TZ]/', '', $value['end'] ?? '*'),
            ];
        });

        $resolver->setNormalizer('search_context', function (Options $options, $value) {
            return trim((string) $value);
        });

        $resolver->setDefaults([
            'contenttypes' => [],
            'exclude_contenttypes' => [],
            'channels' => [],
            'brands' => [],
            'authors' => [],
            'pub_channels' => [],
            'properties' => [],
        ]);

        $arrayNormalizer = function (Options $options, $value) {
            if (\is_string($value)) {
                $value = trim($value);

                return '' !== $value ? [$value] : [];
            }

            if (\is_array($value)) {
                $values = [];

                foreach ($value as $item) {
                    if (!\is_string($item)) {
                        continue;
                    }

                    $item = trim($item);
                    if ('' !== $item) {
                        $values[] = $item;
                    }
                }

                return $values;
            }

            return [];
        };

        $resolver->setNormalizer('contenttypes', $arrayNormalizer);
        $resolver->setNormalizer('exclude_contenttypes', $arrayNormalizer);
        $resolver->setNormalizer('channels', $arrayNormalizer);
        $resolver->setNormalizer('brands', $arrayNormalizer);
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

        // handle start/end dates
        $resolver->setDefaults([
            'start' => null,
            'end' => null,
        ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function isContentChoiceSearch(array $options): bool
    {
        return ($options['search_context'] ?? '') === 'filterable_content_choice';
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getQueryFields(array $options): string
    {
        return 'title content';
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getMinimumMatch(array $options): string
    {
        if ($this->isContentChoiceSearch($options)) {
            return '100%';
        }

        return '75%';
    }

    /**
     * @param array<string, string> $brands
     */
    private function buildContentChoiceQuery(Query $query, string $search, array $brands): string
    {
        $search = $this->stripMatchedBrandNames($search, $brands);
        $terms = preg_split('/\s+/', trim($search)) ?: [];
        $terms = array_filter(array_map('trim', $terms));

        if (!$terms) {
            return '*:*';
        }

        $helper = $query->getHelper();

        return implode(' ', array_map(function (string $term) use ($helper): string {
            if (preg_match('/^[a-f0-9]{32}$/i', $term)) {
                return $helper->escapeTerm($term);
            }

            return $helper->escapeTerm($term).'*';
        }, $terms));
    }

    /**
     * @return array<string, string>
     */
    private function extractMatchingBrands(string $search): array
    {
        $needle = mb_strtolower(trim($search));
        if ($needle === '') {
            return [];
        }

        $repository = $this->manager->getRepository(Brand::class);
        $matched = [];

        foreach ($repository->findAll() as $brand) {
            if (!$brand instanceof Brand) {
                continue;
            }

            $name = trim($brand->getName());
            if ($name === '') {
                continue;
            }

            if (mb_stripos($needle, mb_strtolower($name)) !== false) {
                $matched[$brand->getId()] = $name;
            }
        }

        uasort($matched, static fn (string $left, string $right): int => mb_strlen($right) <=> mb_strlen($left));

        return $matched;
    }

    /**
     * @param array<string, string> $brands
     */
    private function stripMatchedBrandNames(string $search, array $brands): string
    {
        if (!$brands) {
            return $search;
        }

        foreach ($brands as $name) {
            if ($name === '') {
                continue;
            }

            $search = preg_replace('/\b'.preg_quote($name, '/').'\b/i', ' ', $search) ?? $search;
        }

        return preg_replace('/\s+/', ' ', trim($search)) ?? trim($search);
    }
}
