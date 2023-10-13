<?php

namespace Integrated\Bundle\BrandBundle\Services;

use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Services\PublicationSettingsProvider;
use Integrated\Common\Content\Channel\ChannelInterface;

/**
 * @deprecated
 * @todo Use channel type instead
 */
final class LinkTypeSettingsProvider implements PublicationSettingsProvider
{
    public function __construct(
        private readonly BrandRepository $brands,
        private readonly PublicationSettingsProvider $fallback,
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
