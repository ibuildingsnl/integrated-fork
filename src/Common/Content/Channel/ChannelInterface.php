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

use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Common\Security\PermissionInterface;

interface ChannelInterface
{
    public function getId(): ?string;

    public function getName(): string;

    public function getType(): string;

    public function canBePrimary(): bool;

    /** @return PermissionInterface[] */
    public function getPermissions(): iterable;

    public function getLogo(): ?Image;

    public function getColor(): ?string;

    public function getOptions(): array;

    public function getOption(string $name): mixed;
}
