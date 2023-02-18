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

use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeInterface;

/**
 * @author Marijn Otte <marijn@e-active.nl>
 *
 * @description Add usefull properties for filtering
 */
class PremiumType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerInterface $container, $data, array $options = [])
    {
        if (!$data instanceof ContentInterface) {
            return; // only process content
        }

        $featured = $data->isPremium();

        if ($featured) {
            $container->add('facet_properties', 'Premium');
        } else {
            $container->add('facet_properties', 'Not Premium');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integrated.premium';
    }
}
