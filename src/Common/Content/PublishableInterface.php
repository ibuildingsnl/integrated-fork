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

interface PublishableInterface
{
    /**
     * Get the publishing time of the document.
     */
    public function getPublishTime(): PublishTimeInterface;

    /**
     * Set the publishing time of the document.
     *
     * @return $this
     */
    public function setPublishTime(PublishTimeInterface $publishTime);

    /**
     * Check whether the document is published.
     *
     * @param bool $checkPublishTime
     */
    public function isPublished($checkPublishTime = true): bool;
}
