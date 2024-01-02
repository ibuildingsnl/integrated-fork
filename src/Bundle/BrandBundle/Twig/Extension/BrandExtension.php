<?php

namespace Integrated\Bundle\BrandBundle\Twig\Extension;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
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
            new TwigFilter('integrated_brand_profile', [$this, 'getBrandProfileForChannel']),
            new TwigFilter('integrated_brand_website_channel', [$this, 'getBrandWebsiteChannel']),
        ];
    }

    public function getBrandForChannel(?ChannelInterface $channel): ?Brand
    {
        if ($channel instanceof ChannelInterface) {
            foreach ($this->brands->all() as $brand) {
                if ($brand->hasChannel($channel)) {
                    return $brand;
                }
            }
        }

        return null;
    }

    public function getBrandProfileForChannel(?ChannelInterface $channel): ?BrandProfile
    {
        if ($channel instanceof ChannelInterface) {
            foreach ($this->brands->all() as $brand) {
                if ($brand->hasChannel($channel)) {
                    return $brand->profile;
                }
            }
        }

        return null;
    }

    public function getBrandWebsiteChannel(?ChannelInterface $channel): ?Channel
    {
        if ($channel instanceof ChannelInterface) {
            foreach ($this->brands->all() as $brand) {
                if ($brand->hasChannel($channel)) {
                    return $brand->getWebsiteChannel();
                }
            }
        }

        return null;
    }
}
