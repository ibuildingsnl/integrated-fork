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
        'attr' => ['style' => 'editor', 'state' => 'show'],
        'allow_clear' => true,
    ], location: 'editor')]
    protected $parent_id;

    /**
     * @var string|null
     */
    #[Type\Field(type: 'Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType', options: [
        'priority' => 260,
        'label' => 'Link to Channel',
        'attr' => [
            'style' => 'sidebar',
            'help_text' => '<span>Select a channel you want to link this taxonomy to. Should only be done with top level parents.</span>',
        ],
    ], location: 'sidebar')]
    protected $link_to_channel;


    public function getParentID(): ?string
    {
        return $this->parent_id;
    }

    public function setParentID(string $parent_id = null): void
    {
        $this->parent_id = $parent_id;
    }

    public function getLinkToChannel(): ?string
    {
        return $this->link_to_channel;
    }

    public function setLinkToChannel(string $link_to_channel = null): void
    {
        $this->link_to_channel = $link_to_channel;
    }
}
