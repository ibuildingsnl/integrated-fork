<?php

namespace Integrated\Bundle\BrandBundle\Twig\Extension;

use Doctrine\Common\Collections\ArrayCollection;
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
            new TwigFilter('integrated_brands', [$this, 'getAllBrands']),
            new TwigFilter('integrated_other_brands', [$this, 'getAllOtherBrands']),
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

    public function getAllBrands(): ?array
    {
        return $this->brands->all();
    }

    public function getAllOtherBrands(?ChannelInterface $channel): ?ArrayCollection
    {
        $brands = new ArrayCollection();
        if ($channel instanceof ChannelInterface) {
            foreach ($this->brands->all() as $brand) {
                if (!$brand->hasChannel($channel)) {
                    $brands->add($brand);
                }
            }
        }

        return $brands;
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
