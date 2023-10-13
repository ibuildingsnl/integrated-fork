<?php

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Form\Type\MediaGalleryType;
use Integrated\Bundle\ContentBundle\Form\Type\SocialsType;
use Integrated\Bundle\FormTypeBundle\Form\Type\CollectionType;
use Integrated\Bundle\FormTypeBundle\Form\Type\ColorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class BrandProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'priority' => 990,
            'constraints' => new Length(['max' => 100]),
            'attr' => [
                'location' => 'editor',
                'style' => 'inline',
            ],
        ]);

        $builder->add(
            $builder->create('colors', FormType::class, [
                'inherit_data' => true,
                'attr' => [
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'icon' => 'droplet',
                    'state' => 'show',
                ],
            ])->add(
                'color',
                ColorType::class,
                [
                    'label' => 'Primary Color',
                    'required' => false,
                ]
            )->add(
                'secondaryColor',
                ColorType::class,
                [
                    'label' => 'Secondary Color',
                    'required' => false,
                ]
            )
        );

        $builder->add(
            'logo',
            MediaGalleryType::class,
            [
                'attr' => [
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'icon' => 'media-image',
                    'data-types' => '[{"type":"image","name":"Image"}]',
                    'data-emptytext' => 'Select logo',
                    'data-multiple' => false,
                    'state' => 'show',
                ],
            ]
        );

        $builder->add('contacts', CollectionType::class, [
            'entry_type' => 'Integrated\Bundle\ContentBundle\Form\Type\ContactType',
            'priority' => 490,
            'allow_add' => true,
            'allow_delete' => true,
            'add_button_text' => 'Add contact',
            'label' => 'Address',
            'attr' => ['location' => 'editor', 'style' => 'editor', 'state' => 'show'],
        ]);

        $builder->add('socials', SocialsType::class, ['priority' => 480]);

        $builder->add(
            $builder->create('company_data', FormType::class, [
                'inherit_data' => true,
                'attr' => [
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'icon' => 'city',
                    'state' => 'show',
                ],
            ])->add(
                'companyId',
                TextType::class,
                [
                    'label' => 'Company ID',
                    'required' => false,
                ]
            )->add(
                'vat',
                TextType::class,
                [
                    'required' => false,
                ]
            )
        );

        $builder->add('analytics', TextType::class, [
            'label' => 'Analytics ID',
            'attr' => [
                'location' => 'sidebar',
                'style' => 'sidebar',
                'icon' => 'graph-up',
                'state' => 'show',
            ],
            'required' => false,
        ]);
    }
}
