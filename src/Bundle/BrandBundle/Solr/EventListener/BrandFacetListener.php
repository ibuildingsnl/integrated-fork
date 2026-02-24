<?php

namespace Integrated\Bundle\BrandBundle\Solr\EventListener;

use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Common\Solr\Search\Event\ConfigureOptionsEvent;
use Integrated\Common\Solr\Search\Event\PostCreateEvent;
use Integrated\Common\Solr\Search\QueryEvents;
use Solarium\Component\Facet\Field;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BrandFacetListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            QueryEvents::CONFIGURE_OPTIONS => 'addBrandOption',
            QueryEvents::POST_CREATE => 'addBrandFilterFacet',
        ];
    }

    public function addBrandOption(ConfigureOptionsEvent $event): void
    {
        if ($event->getFormType() !== IntegratedContent::class) {
            return;
        }
        $resolver = $event->getResolver();

        $resolver->setDefault('brands', []);
    }

    public function addBrandFilterFacet(PostCreateEvent $event): void
    {
        $query = $event->getQuery();
        $options = $event->getOptions();
        $facet = $query->getFacetSet();

        /** @var Field $field */
        $field = $facet->createFacetField('brands', false);
        $field->setField('facet_brands')->getLocalParameters()->setExclude('brands');

        $facets = array_merge(
            \array_slice($facet->getFacets(), 0, 1),
            ['brands' => $field],
            \array_slice($facet->getFacets(), 1)
        );
        $facet->setFacets($facets);

        $brands = $this->sanitizeListValues($options['brands'] ?? []);
        if (\count($brands)) {
            $query->createFilterQuery('brands')->addTag('brands')->setQuery('facet_brands: ((%1%))', [
                implode(') OR (', array_map(
                    fn ($x) => $query->getHelper()->escapePhrase($x),
                    $brands
                )),
            ]);
        }
    }

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
