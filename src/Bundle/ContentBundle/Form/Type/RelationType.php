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

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class RelationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => [
                        'Embedded' => 'embedded',
                        'Cover' => 'cover',
                        'Taxonomy' => 'taxonomy',
                        'Category' => 'category',
                        'Edition' => 'edition',
                        'Commercial' => 'commercial',
                        'Parent' => 'parent',
                        'Subscriptiontypes' => 'subscriptiontypes',
                        'Author' => 'author',
                        'Multimedia - Single' => 'mmsingle',
                        'Multimedia - Multi' => 'mmmulti',
                        'Multimedia' => 'multimedia',
                        'File' => 'file',
                        'Slider' => 'slider',
                    ],
                ]
            )->add(
                'sources',
                DocumentType::class,
                [
                    'class' => ContentType::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'required' => false,
                ]
            )->add(
                'targets',
                DocumentType::class,
                [
                    'class' => ContentType::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'required' => false,
                ]
            )->add(
                'location',
                ChoiceType::class,
                [
                    'choices' => [
                        'Sidebar' => Embedded\Relation::LOCATION_SIDEBAR,
                        'Editor' => Embedded\Relation::LOCATION_EDITOR,
                    ],
                ]
            )->add(
                'icon',
                null,
                [
                    'attr' => [
                        'help_text' => '<span>You can use any <a href="https://iconoir.com/" target="_blank">Iconoir</a> icon</span>',
                    ],
                ]
            )
            ->add(
                $builder->create('options', FormType::class, ['inherit_data' => true])
                        ->add(
                            'multiple',
                            CheckboxType::class,
                            [
                                'label' => 'Allow multiselect',
                                'required' => false,
                                'attr' => [
                                    'align_with_widget' => true,
                                ],
                            ]
                        )
                        ->add(
                            'required',
                            CheckboxType::class,
                            [
                                'label' => 'This relation is required',
                                'required' => false,
                                'attr' => [
                                    'align_with_widget' => true,
                                ],
                            ]
                        )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Relation\\Relation',
                               ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_relation';
    }
}
