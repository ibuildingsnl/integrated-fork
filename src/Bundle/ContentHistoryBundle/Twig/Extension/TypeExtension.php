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

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class TypeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('get_type', $this->getType(...)),
            new TwigFilter('get_class', $this->getClass(...)),
        ];
    }

    /**
     * @return string
     */
    public function getType($value)
    {
        return \gettype($value);
    }

    /**
     * @return string
     */
    public function getClass($value)
    {
        return $value::class;
    }

    public function getName(): string
    {
        return 'integrated_content_history_type';
    }
}
