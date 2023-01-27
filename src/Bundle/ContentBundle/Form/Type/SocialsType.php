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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SocialsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (\in_array('icon', $options['fields'])) {
            $builder->add('icon', ChoiceType::class, [
                'label' => $options['label_icon'],
                'choices' => [
                    'Default' => 'default',
                    'Website' => 'website',
                    'Facebook' => 'facebook',
                    'Twitter' => 'twitter',
                    'LinkedIn' => 'linkedin',
                    'Instagram' => 'instagram'
                ],
            ]);
        }

        if (\in_array('url', $options['fields'])) {
            $builder->add('url', TextType::class, [
                'label' => $options['label_url'],
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Embedded\\Socials',
            'fields' => ['icon', 'url'], // @todo validate options (INTEGRATED-627)
            'label_icon' => 'Icon',
            'label_url' => 'URL',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_social';
    }
}
