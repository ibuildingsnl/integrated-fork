<?php

namespace Integrated\Bundle\BrandBundle\Services;

use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Services\PublicationSettingsProviderInterface;
use Integrated\Common\Content\Channel\ChannelInterface;

class LinkTypeSettingsProvider implements PublicationSettingsProviderInterface
{
    public function __construct(
        private readonly BrandRepository $brands,
        private readonly PublicationSettingsProviderInterface $fallback,
    ) {
    }

    public function settingTypeFor(ChannelInterface $channel): string
    {
        foreach ($this->brands->all() as $brand) {
            if ($brand->hasChannel($channel) && $form = $brand->linkTypeForChannel($channel)->publicationSettingsForm) {
                return $form;
            }
        }

        return $this->fallback->settingTypeFor($channel);
    }
}
