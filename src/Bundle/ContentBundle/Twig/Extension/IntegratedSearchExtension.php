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

use Integrated\Bundle\ContentBundle\Solr\Query\SortOption;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOptions;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IntegratedSearchExtension extends AbstractExtension
{
    private SortOptions $options;

    public function __construct(SortOptions $options)
    {
        $this->options = $options;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('integrated_search_sorting_options', $this->getSortingOptions(...)),
            new TwigFunction('integrated_search_sorting_option', $this->getSortingOption(...)),
        ];
    }

    /**
     * @return SortOption[]
     */
    public function getSortingOptions(): array
    {
        return $this->options->all();
    }

    public function getSortingOption(string $name): ?SortOption
    {
        if ($this->options->hasByField($name)) {
            return $this->options->getByField($name);
        }

        return null;
    }
}
