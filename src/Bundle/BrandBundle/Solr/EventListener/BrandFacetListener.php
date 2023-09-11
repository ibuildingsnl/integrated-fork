<?php

namespace Integrated\Bundle\BrandBundle\Solr\EventListener;

use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Common\Solr\Search\Event\ConfigureOptionsEvent;
use Integrated\Common\Solr\Search\Event\PostCreateEvent;
use Integrated\Common\Solr\Search\QueryEvents;
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

        $field = $facet->createFacetField('brands', false);
        $field->setField('facet_brands')->getLocalParameters()->setExclude('brands');

        $facets = array_merge(
            \array_slice($facet->getFacets(), 0, 1),
            ['brands' => $field],
            \array_slice($facet->getFacets(), 1)
        );
        $facet->setFacets($facets);

        if ($options['brands'] ?? false) {
            $query->createFilterQuery('brands')->addTag('brands')->setQuery('facet_brands: ((%1%))', [
                implode(') OR (', array_map(
                    fn ($x) => $query->getHelper()->escapePhrase($x),
                    $options['brands']
                )),
            ]);
        }
    }
}
