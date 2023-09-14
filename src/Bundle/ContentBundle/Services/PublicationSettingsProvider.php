<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Common\Channel\ChannelInterface;

interface PublicationSettingsProvider
{
    public function settingTypeFor(ChannelInterface $channel): string;
}
