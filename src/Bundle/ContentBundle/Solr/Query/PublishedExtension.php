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

use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContentBlock;
use Integrated\Common\Solr\Search\Type\AbstractTypeExtension;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublishedExtension extends AbstractTypeExtension
{
    public function build(Query $query, array $options): void
    {
        if ($options['published']) {
            $query
                ->createFilterQuery('pub')
                ->setQuery('pub_active: 1 AND pub_time:[* TO NOW] AND pub_end:[NOW TO *]');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'published' => false,
        ]);

        $resolver->addAllowedTypes('published', 'boolean');
    }

    public static function getTypes(): iterable
    {
        return [
            IntegratedContentBlock::class,
        ];
    }
}
