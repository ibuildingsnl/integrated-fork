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

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class JsonExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('json_decode', [$this, 'decode'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function decode($value)
    {
        return json_decode($value, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integrated_content_json_extension';
    }
}
