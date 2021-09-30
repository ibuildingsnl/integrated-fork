<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFilter;
/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class ArrayExtension extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('unset_value', function (array $array, string $value) : array {
                return $this->unsetValue($array, $value);
            }),
        ];
    }

    /**
     * @param array  $array
     * @param string $value
     *
     * @return array
     */
    public function unsetValue($array, $value)
    {
        return array_diff($array, [$value]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integrated_content_array_extension';
    }
}
