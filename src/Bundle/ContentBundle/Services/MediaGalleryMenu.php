<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Services;
/**
 * Class MediaGalleryMenu.
 *
 * @author Wouter Koppers <wouter@twindigital.com>
 */
class MediaGalleryMenu
{
    public function getChannels() {
        return [1, 2, 3];
    }

    public function getTaxonomyItems() {
        return [4, 5, 6];
    }

    public function get() {
        return array_merge($this->getChannels(), $this->getTaxonomyItems());
    }
}
