<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Content\Channel;

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Common\Security\PermissionInterface;

interface ChannelInterface
{
    public function getId(): ?string;

    public function getName(): ?string;

    public function getType(): ?ChannelType;

    /** @return PermissionInterface[] */
    public function getPermissions(): iterable;

    public function getPrimaryDomain(): ?string;

    public function getPrimaryDomainRedirect(): bool;
}
