<?php

namespace Integrated\Bundle\ContentBundle\Twig\Extension;

use Integrated\Bundle\ContentBundle\Twig\FacetSettings;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FacetExtension extends AbstractExtension
{
    /** @var FacetSettings[] */
    private array $settings;

    /** @param FacetSettings[] $facetSettings */
    public function __construct(iterable $facetSettings)
    {
        foreach ($facetSettings as $settings) {
            $this->settings[$settings->tag] = $settings;
        }
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'integrated_facet_show',
                fn($key) => isset($this->settings[$key]) ? $this->settings[$key]->show : false,
            ),
            new TwigFunction(
                'integrated_facet_title',
                fn($key, $id) => isset($this->settings[$key]) ? $this->settings[$key]->titleFor($id) : $id,
            ),
        ];
    }
}
