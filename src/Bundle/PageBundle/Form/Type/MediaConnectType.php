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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MediaConnectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('categoryID', TextType::class, [
            'label' => 'Category ID',
        ]);
        $builder->add('channelID', TextType::class, [
            'label' => 'Channel ID',
        ]);
        $builder->add('mediaIDs', TextType::class, [
            'label' => 'Media ID`s (one or multiple)',
            'required' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
//        $resolver->setRequired(['mediaIDs']);
//        $resolver->setAllowedTypes('mediaIDs', 'array');
//        $resolver->setAllowedTypes('categoryID', 'string');
//        $resolver->setAllowedTypes('channelID', 'string');
    }
}
