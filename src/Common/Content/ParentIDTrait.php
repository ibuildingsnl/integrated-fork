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

use Integrated\Common\Form\Mapping\Attributes as Type;

trait ParentIDTrait
{
    /**
     * @var string|null
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\ContentParentIDType', options: [
        'priority' => 260,
        'label' => 'Parent',
        'route' => 'integrated_content_parent_id_lookup',
        'attr' => ['location' => 'sidebar', 'style' => 'sidebar', 'state' => 'show'],
    ])]
    protected $parent_id;

    public function getParentID(): ?string
    {
        return $this->parent_id;
    }

    public function setParentID(string $parent_id = null): void
    {
        $this->parent_id = $parent_id;
    }
}
