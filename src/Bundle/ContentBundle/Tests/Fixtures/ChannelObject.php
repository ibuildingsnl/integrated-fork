<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Fixtures;

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Common\Content\Channel\ChannelInterface;

class ChannelObject implements ChannelInterface
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
        throw new \Exception();
    }

    public function getPermissions(): iterable
    {
        throw new \Exception();
    }

    public function getPrimaryDomain(): ?string
    {
        throw new \Exception();
    }

    public function getPrimaryDomainRedirect(): bool
    {
        throw new \Exception();
    }

    public function getLogo(): ?Image
    {
        throw new \Exception();
    }

    public function getColor(): ?string
    {
        throw new \Exception();
    }

    public function getType(): ?ChannelType
    {
        throw new \Exception();
    }
}
