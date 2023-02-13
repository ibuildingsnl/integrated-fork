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

interface PremiumInterface
{
    /**
     * Set the premium status of the document.
     *
     * @param bool $premium
     *
     * @return $this
     */
    public function setPremium($premium);

    /**
     * Get the premium status of the document.
     *
     * @param bool $premium
     */
    public function isPremium();
}
