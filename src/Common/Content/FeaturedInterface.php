<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Content;

interface FeaturedInterface
{
    /**
     * Set the featured status of the document.
     */
    public function setFeatured(bool $featured): static;

    /**
     * Get the featured status of the document.
     */
    public function isFeatured(): ?bool;
}
