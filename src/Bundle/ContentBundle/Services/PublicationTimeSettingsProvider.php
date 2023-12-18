<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Bundle\ContentBundle\Form\Type\DefaultPublicationSettingsType;
use Integrated\Common\Content\Channel\ChannelInterface;

class PublicationTimeSettingsProvider implements PublicationSettingsProvider
{
    public function settingTypeFor(ChannelInterface $channel): string
    {
        return DefaultPublicationSettingsType::class;
    }
}
