<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Connector;

use Integrated\Common\Channel\Exporter\ExporterResponse;
use Integrated\Common\Content\Channel\ChannelInterface;

interface ExporterInterface
{
    public const STATE_ADD = 'add';

    public const STATE_DELETE = 'delete';

    public function export(object $content, string $state, ChannelInterface $channel, array $settings = []): ?ExporterResponse;
}
