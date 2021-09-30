<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentHistoryBundle\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFilter;
/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class TypeExtension extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('get_type', function ($value) : string {
                return $this->getType($value);
            }),
            new Twig_SimpleFilter('get_class', function ($value) : string {
                return $this->getClass($value);
            }),
        ];
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function getType($value)
    {
        return \gettype($value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function getClass($value)
    {
        return \get_class($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integrated_content_history_type';
    }
}
