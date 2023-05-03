<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Tests\Fixtures;

use Integrated\Common\Channel\ChannelInterface;

class Channel implements ChannelInterface
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        throw new \LogicException('Not implemented');
    }

    public function getPermissions()
    {
        throw new \LogicException('Not implemented');
    }

    public function getPrimaryDomain()
    {
        throw new \LogicException('Not implemented');
    }

    public function getPrimaryDomainRedirect()
    {
        throw new \LogicException('Not implemented');
    }
}
