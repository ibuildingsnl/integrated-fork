<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\DataFixtures\Faker\Provider;

use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Common\Content\Channel\ChannelInterface;

class ChannelProvider
{
    public function __construct(
        private readonly ChannelRepository $channels,
    ) {
    }

    /** @throws DocumentNotFoundException */
    public function channel(string $id): ChannelInterface
    {
        $channel = $this->channels->find($id);

        if (!$channel) {
            throw DocumentNotFoundException::documentNotFound(Channel::class, $id);
        }

        return $channel;
    }
}
