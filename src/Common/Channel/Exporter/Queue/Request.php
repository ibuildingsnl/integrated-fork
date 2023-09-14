<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Exporter\Queue;

use Integrated\Common\Channel\ChannelInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Request
{
    public function __construct(
        public ?object $content = null,
        public ?string $state = null,
        public ?ChannelInterface $channel = null,
    ) {
    }
}
