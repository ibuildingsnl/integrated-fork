<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Common\Content\Channel\ChannelInterface;

interface PublicationSettingsProviderInterface
{
    public function settingTypeFor(ChannelInterface $channel): string;
}
