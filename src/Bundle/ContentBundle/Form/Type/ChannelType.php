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

use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\ContentBundle\Infrastructure\ChannelTypeRegistry;
use Integrated\Bundle\FormTypeBundle\Form\Type\TailwindCollectionType;
use Integrated\Bundle\UserBundle\Model\Scope;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ChannelType extends AbstractType
{
    public function __construct(
        private readonly AssetManager $js,
        private readonly ChannelTypeRegistry $channelTypes,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', ChoiceType::class, [
            'choices' => $this->channelTypes->allTypes(),
            'choice_label' => 'name',
            'choice_value' => 'id',
            'priority' => 1000,
            'attr' => [
                'location' => 'editor',
                'style' => 'inline',
                'class' => $options['can_change_type'] ? '' : 'hidden',
            ],
        ]);
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

        $builder->add('domains', TailwindCollectionType::class, [
            'priority' => 500,
            'label' => 'Domains (example.com)',
            'allow_add' => true,
            'allow_delete' => true,
            'add_button_text' => 'Add domain',
            'delete_button_text' => 'Delete domain',
            'attr' => [
                'class' => 'channel-domains',
                'show_headings' => 'false',
                'location' => 'editor',
                'style' => 'editor',
                'state' => 'show',
                'data-exclusive-to' => 'website',
            ],
        ]);

        $builder->add('primaryDomain', HiddenType::class, [
            'priority' => 500,
            'attr' => [
                'class' => 'primary-domain-input',
                'data-exclusive-to' => 'website',
            ],
        ]);

        $builder->add(
            $builder->create('permissions', FormType::class, [
                'inherit_data' => true,
                'attr' => [
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'icon' => 'key-back',
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
            $builder->create('channel_options', FormType::class, [
                'inherit_data' => true,
                'attr' => [
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'state' => 'show',
                    'icon' => 'tools',
                    'data-exclusive-to' => 'website',
                ],
            ])->add(
                'primaryDomainRedirect',
                CheckboxSwitcherType::class,
                [
                    'label' => 'Redirect to primary domain',
                    'required' => false,
                    'attr' => [
                        'align_with_widget' => true,
                        'data-exclusive-to' => 'website',
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
                        'data-exclusive-to' => 'website',
                    ],
                ]
            )->add(
                'language',
                LanguageType::class,
                [
                    'label' => 'Website language',
                    'required' => false,
                    'choice_self_translation' => true,
                    'attr' => [
                        'align_with_widget' => true,
                        'data-exclusive-to' => 'website',
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

        $this->js->add('bundles/integratedcontent/js/channel_types.js');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('can_change_type', true);
    }
}
