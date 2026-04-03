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
    /** @var Brand[]|null */
    private ?array $allBrands = null;

    /** @var array<string, Brand|null> */
    private array $brandByChannel = [];

    /** @var array<string, BrandProfile|null> */
    private array $profileByChannel = [];

    /** @var array<string, Channel|null> */
    private array $websiteChannelByChannel = [];

    /** @var array<string, ArrayCollection> */
    private array $otherBrandsByChannel = [];

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
            new TwigFilter('integrated_brand', $this->getBrandForChannel(...)),
            new TwigFilter('integrated_brands', $this->getAllBrands(...)),
            new TwigFilter('integrated_other_brands', $this->getAllOtherBrands(...)),
            new TwigFilter('integrated_brand_profile', $this->getBrandProfileForChannel(...)),
            new TwigFilter('integrated_brand_website_channel', $this->getBrandWebsiteChannel(...)),
        ];
    }

    public function getBrandForChannel(?ChannelInterface $channel): ?Brand
    {
        if (!$channel instanceof ChannelInterface) {
            return null;
        }

        $channelKey = $this->getChannelCacheKey($channel);
        if (\array_key_exists($channelKey, $this->brandByChannel)) {
            return $this->brandByChannel[$channelKey];
        }

        foreach ($this->getBrands() as $brand) {
            if ($brand->hasChannel($channel)) {
                return $this->brandByChannel[$channelKey] = $brand;
            }
        }

        return $this->brandByChannel[$channelKey] = null;
    }

    public function getAllBrands(): ?array
    {
        return $this->getBrands();
    }

    public function getAllOtherBrands(?ChannelInterface $channel): ?ArrayCollection
    {
        $brands = new ArrayCollection();
        if (!$channel instanceof ChannelInterface) {
            return $brands;
        }

        $channelKey = $this->getChannelCacheKey($channel);
        if (\array_key_exists($channelKey, $this->otherBrandsByChannel)) {
            return $this->otherBrandsByChannel[$channelKey];
        }

        foreach ($this->getBrands() as $brand) {
            if (!$brand->hasChannel($channel)) {
                $brands->add($brand);
            }
        }

        return $this->otherBrandsByChannel[$channelKey] = $brands;
    }

    public function getBrandProfileForChannel(?ChannelInterface $channel): ?BrandProfile
    {
        if (!$channel instanceof ChannelInterface) {
            return null;
        }

        $channelKey = $this->getChannelCacheKey($channel);
        if (\array_key_exists($channelKey, $this->profileByChannel)) {
            return $this->profileByChannel[$channelKey];
        }

        $brand = $this->getBrandForChannel($channel);

        return $this->profileByChannel[$channelKey] = $brand?->profile;
    }

    public function getBrandWebsiteChannel(?ChannelInterface $channel): ?Channel
    {
        if (!$channel instanceof ChannelInterface) {
            return null;
        }

        $channelKey = $this->getChannelCacheKey($channel);
        if (\array_key_exists($channelKey, $this->websiteChannelByChannel)) {
            return $this->websiteChannelByChannel[$channelKey];
        }

        $brand = $this->getBrandForChannel($channel);

        return $this->websiteChannelByChannel[$channelKey] = $brand?->getWebsiteChannel();
    }

    /** @return Brand[] */
    private function getBrands(): array
    {
        if (null === $this->allBrands) {
            $this->allBrands = $this->brands->all();
        }

        return $this->allBrands;
    }

    private function getChannelCacheKey(ChannelInterface $channel): string
    {
        return (string) ($channel->getId() ?? spl_object_id($channel));
    }
}
