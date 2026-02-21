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

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeInterface;

/**
 * @author Bas Hosman <bas@twindigital.nl>
 *
 * @description Add usefull properties for filtering
 */
class FeaturedType implements TypeInterface
{
    public function build(ContainerInterface $container, $data, array $options = [])
    {
        if (!$data instanceof Content) {
            return; // only process content
        }

        // Add property for has image / doesn't have image (usefull to make selections with articles for views with image, or to find articles with missing image)
        $featured = $data->isFeatured();

        if ($featured) {
            $container->add('facet_properties', 'Featured');
            $container->set('featured', true);
        } else {
            $container->add('facet_properties', 'Not Featured');
            $container->set('featured', false);
        }
    }

    public function getName()
    {
        return 'integrated.featured';
    }
}
