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
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SocialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (\in_array('url', $options['fields'])) {
            $builder->add('url', UrlType::class, [
                'default_protocol' => 'https',
                'label' => $options['label_url'],
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Embedded\\Social',
            'fields' => ['url'], // @todo validate options (INTEGRATED-627)
            'label_url' => 'URL',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'integrated_social';
    }
}
