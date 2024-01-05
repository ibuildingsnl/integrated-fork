<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ChannelBundle\Model;

use Integrated\Common\Channel\Connector\Config\Options as BaseOptions;

class Options extends BaseOptions implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
