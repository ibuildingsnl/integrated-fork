<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ContentTypeChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ChannelInterface $channel */
        $channel = $options['channel'];

        $builder->add('selected', CheckboxType::class, [
            'required' => false,
            'label' => $channel->getName(),
        ]);

        $builder->add('restrict', CheckboxType::class, [
            'required' => false,
        ]);

        $builder->add('enforce', CheckboxType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['channel']);
        $resolver->setAllowedTypes('channel', 'Integrated\\Bundle\\ContentBundle\\Document\\Channel\\Channel');
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_type_channel';
    }
}
