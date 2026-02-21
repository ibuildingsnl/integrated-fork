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
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeInterface;

class CustomFieldsType implements TypeInterface
{
    public function build(ContainerInterface $container, $data, array $options = [])
    {
        if (!$data instanceof Content) {
            return; // only process content
        }

        $customFields = $data->getCustomFields();

        foreach ($customFields as $key => $value) {
            $container->add($key, $value);
        }
    }

    public function getName()
    {
        return 'integrated.customFields';
    }
}
