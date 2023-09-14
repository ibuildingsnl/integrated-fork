<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Bundle\ContentBundle\Form\Type\PublishTimeType;
use Integrated\Common\Channel\ChannelInterface;

class PublicationTimeSettingsProvider implements PublicationSettingsProvider
{
    public function settingTypeFor(ChannelInterface $channel): string
    {
        return PublishTimeType::class;
    }
}
