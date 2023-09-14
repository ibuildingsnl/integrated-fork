<?php

namespace Integrated\Bundle\BrandBundle\Services;

use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Services\PublicationSettingsProvider;
use Integrated\Common\Channel\ChannelInterface;

// @todo configure
final class LinkTypeSettingsProvider implements PublicationSettingsProvider
{
    public function __construct(
        private readonly BrandRepository $brands,
        private readonly PublicationSettingsProvider $fallback,
    ){
    }

    public function settingTypeFor(ChannelInterface $channel): string
    {
        foreach ($this->brands->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                foreach ($brand->getChannelLinks() as $link) {
                    if ($link->channel === $channel && $link->type->publicationSettingsForm) {
                        return $link->type->publicationSettingsForm;
                    }
                }
            }
        }
        return $this->fallback->settingTypeFor($channel);
    }
}
