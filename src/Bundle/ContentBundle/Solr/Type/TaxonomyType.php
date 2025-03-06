<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Solr\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeInterface;

class TaxonomyType implements TypeInterface
{
    public function __construct(
        private readonly DocumentManager $documentManager,
    ) {
    }

    public function build(ContainerInterface $container, $data, array $options = [])
    {
        if (!$data instanceof Content) {
            return; // only process content
        }

        // Relation field and facet field for taxonomy, commercial and edition relations
        $items = array_merge(
            $data->getRelationsByRelationType('taxonomy'),
            $data->getRelationsByRelationType('taxonomy_category'),
            $data->getRelationsByRelationType('commercial'),
            $data->getRelationsByRelationType('edition')
        );

        foreach ($items as $relation) {
            foreach ($relation->getReferences() as $content) {
                if (($content instanceof Taxonomy || $content instanceof Article) && $content->getTitle()) {
                    $container->add('facet_'.$relation->getRelationId(), $content->getTitle());

                    if ($content instanceof Taxonomy) {
                        $container->add('taxonomy_'.$relation->getRelationId().'_string', $content->getTitle());
                        foreach ($content->getChannels() as $channel) {
                            $childrenCount = $this->documentManager
                                ->getRepository(Content::class)
                                ->createQueryBuilder()->count()
                                ->field('class')->equals(Taxonomy::class)
                                ->field('parent_id')->equals($content->getId())
                                ->getQuery()
                                ->execute();

                            if ((int) $childrenCount > 0) {
                                $container->add('taxonomy_parent_'.$channel->getId().'_'.$relation->getRelationId().'_string', $content->getTitle());
                            } else {
                                $container->add('taxonomy_child_'.$channel->getId().'_'.$relation->getRelationId().'_string', $content->getTitle());
                            }
                            $container->add('taxonomy_'.$channel->getId().'_'.$relation->getRelationId().'_string', $content->getTitle());
                        }
                    }
                }
            }
        }
    }

    public function getName()
    {
        return 'integrated.taxonomy';
    }
}
