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

use Integrated\Bundle\FormTypeBundle\Form\Type\CollectionType;
use Integrated\Bundle\FormTypeBundle\Form\Type\ColorType;
use Integrated\Bundle\FormTypeBundle\Form\Type\TailwindCollectionType;
use Integrated\Bundle\UserBundle\Model\Scope;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ChannelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
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
            'scope',
            EntityType::class,
            [
                'required' => false,
                'class' => Scope::class,
                'placeholder' => 'No user login allowed',
                'label' => 'User scope',
                'choice_label' => 'name',
                'attr' => [
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'state' => 'show',
                    'icon' => 'precision-tool',
                ],
            ]
        );

        $builder->add(
            $builder->create('colors', FormType::class, [
                'inherit_data' => true,
                'attr' => [
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'state' => 'show',
                    'icon' => 'droplet',
                ],
            ])->add(
                'color',
                ColorType::class,
                [
                    'label' => 'Primary Color',
                    'required' => false,
                ]
            )->add(
                'secondarycolor',
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
                    'state' => 'show',
                    'icon' => 'media-image',
                    'data-types' => '[{"type":"image","name":"Image"}]',
                    'data-emptytext' => 'Select logo',
                    'data-multiple' => false,
                ],
            ]
        );

        $builder->add('domains', TailwindCollectionType::class, [
            'priority' => 500,
            'label' => 'Domains (example.com)',
            'allow_add' => true,
            'allow_delete' => true,
            'add_button_text' => 'Add domain',
            'delete_button_text' => 'Delete domain',
            'attr' => ['class' => 'channel-domains', 'show_headings' => 'false', 'location' => 'editor', 'style' => 'editor', 'state' => 'show'],
        ]);

        $builder->add('primaryDomain', HiddenType::class, [
            'priority' => 500,
            'attr' => ['class' => 'primary-domain-input'], ]);

        $builder->add('contacts', CollectionType::class, [
            'entry_type' => 'Integrated\Bundle\ContentBundle\Form\Type\ContactType',
            'priority' => 490,
            'allow_add' => true,
            'allow_delete' => true,
            'add_button_text' => 'Add contact',
            'label' => 'Address',
            'attr' => ['location' => 'editor', 'style' => 'editor', 'state' => 'show'],
        ]);

        $builder->add('social', TailwindCollectionType::class, [
            'entry_type' => 'Integrated\Bundle\ContentBundle\Form\Type\SocialType',
            'priority' => 480,
            'allow_add' => true,
            'allow_delete' => true,
            'add_button_text' => 'Add social',
            'label' => 'Socials',
            'attr' => ['location' => 'editor', 'style' => 'editor', 'state' => 'show'],
        ]);

        $builder->add(
            $builder->create('permissions', FormType::class, [
                'inherit_data' => true,
                'attr' => [
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'state' => 'show',
                    'icon' => 'key-alt-back',
                ],
            ])->add(
                'permissions',
                PermissionsType::class,
                [
                    'required' => false,
                ]
            )
        );

        $builder->add(
            $builder->create('company_data', FormType::class, [
                'inherit_data' => true,
                'attr' => [
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'icon' => 'city',
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
            ],
        ]);

        $builder->add(
            $builder->create('options', FormType::class, [
                'inherit_data' => true,
                'attr' => [
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'state' => 'show',
                    'icon' => 'tools',
                ],
            ])->add(
                'primaryDomainRedirect',
                CheckboxSwitcherType::class,
                [
                    'label' => 'Redirect to primary domain',
                    'required' => false,
                    'attr' => [
                        'align_with_widget' => true,
                    ],
                ]
            )->add(
                'ipProtected',
                CheckboxSwitcherType::class,
                [
                    'label' => 'Protect by IP address or logged in user',
                    'required' => false,
                    'attr' => [
                        'align_with_widget' => true,
                    ],
                ]
            )
        );

        // validate domain names
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (empty($data['domains'])) {
                return;
            }

            $primaryChannelIsIteratedAndEmpty = false;

            foreach ($data['domains'] as $domain) {
                $domain = trim($domain);
                $primary = trim($data['primaryDomain']);

                if ($domain == '' && ($primaryChannelIsIteratedAndEmpty || $domain != $primary)) {
                    $form->get('domains')->addError(new FormError('Domain name can not be empty (only primary)'));
                } elseif (preg_match('/[\s\\\[\],;:+\/\?^`=&%"\'#<>@*!()|]/', $domain, $matches)) {
                    $form->get('domains')->addError(
                        new FormError(
                            sprintf('Character "%s" in domain name "%s" is not allowed', $matches[0], $domain)
                        )
                    );
                }

                if ($primary == '' && $domain == $primary) {
                    $primaryChannelIsIteratedAndEmpty = true;
                }
            }
        });
    }
}
