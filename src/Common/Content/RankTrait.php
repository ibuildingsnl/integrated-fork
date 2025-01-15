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

trait RankTrait
{
    /**
     * @var string|null
     */
    #[Type\Field(type: 'Integrated\Bundle\FormTypeBundle\Form\Type\ContentRankType', options: [
        'priority' => 250,
        'label' => 'Rank',
        'route' => 'integrated_content_rank_lookup',
        'attr' => [
            'icon' => 'numbered-list-left',
            'style' => 'sidebar',
        ],
    ], location: 'sidebar')]
    protected $rank;

    public function getRank(): ?string
    {
        return $this->rank;
    }

    public function setRank(?string $rank = null)
    {
        $this->rank = $rank;
    }
}
