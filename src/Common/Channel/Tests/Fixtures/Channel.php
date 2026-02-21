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

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Common\Content\Channel\ChannelInterface;

class Channel implements ChannelInterface
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        throw new \LogicException('Not implemented');
    }

    public function getPermissions(): iterable
    {
        throw new \LogicException('Not implemented');
    }

    public function getPrimaryDomain(): ?string
    {
        throw new \LogicException('Not implemented');
    }

    public function getPrimaryDomainRedirect(): bool
    {
        throw new \LogicException('Not implemented');
    }

    public function getLogo(): ?Image
    {
        throw new \LogicException('Not implemented');
    }

    public function getColor(): ?string
    {
        throw new \LogicException('Not implemented');
    }

    public function getType(): ?ChannelType
    {
        throw new \LogicException('Not implemented');
    }
}
