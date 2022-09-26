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

trait ParentIDTrait
{
    /**
     * @var string
     * @Type\Field
     */
    protected $parent_id;

    /**
     * @return string|null
     */
    public function getParentID(): ?string
    {
        return $this->parent_id;
    }

    /**
     * @param string|null $parent_id
     */
    public function setParentID(string $parent_id = null)
    {
        $this->parent_id = $parent_id;
    }
}
