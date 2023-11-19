<?php

namespace Integrated\Bundle\BrandBundle\Twig\Extension;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Common\Content\Channel\ChannelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class BrandExtension extends AbstractExtension
{
    public function __construct(
        private readonly BrandRepository $brands,
    ) {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('integrated_brand', [$this, 'getBrandForChannel']),
        ];
    }

    public function getBrandForChannel(ChannelInterface $channel): ?Brand
    {
        foreach ($this->brands->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                return $brand;
            }
        }

        return null;
    }
}
