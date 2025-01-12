<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Form\Type;

use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PageFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('q', TextType::class, [
            'label' => 'Search query',
            'required' => false,
        ]);

        $builder->add('pagetype', ChoiceType::class, [
            'label' => 'Page type',
            'choices' => [
                'Select a page type' => '',
                'Static pages' => 'page',
                'Content type pages' => 'contenttype',
            ],
            'required' => false,
        ]);

        $builder->add('channel', ChannelChoiceType::class, [
            'label' => 'Channel',
            'required' => false,
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Filter',
        ]);
    }
}
